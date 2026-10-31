<?php

namespace App\Services\Reg;

use App\Models\Student;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * ترحيل طلاب كلية ومستوى وعام دراسي إلى مستوى وعام جديدين، لمن حصل منهم على إحدى توصيات الفصل الثاني.
 * الرصيد المتبقي (xmod) يُرحَّل إلى fees8 ثم تُعاد حسابات الرسوم عبر StudentFeeService.
 */
class StudentPromotionService
{
    public const PERMISSION = 'View:MovingStudent';

    public function __construct(private readonly StudentFeeService $fees) {}

    /**
     * الطلاب المرحَّلون: الاستعلام نفسه للمعاينة والجدول والتنفيذ، ضمن نطاق المستخدم الأكاديمي.
     *
     * @param  array{faculty_code?: mixed, current_level?: mixed, current_batch_code?: mixed, recom_code?: mixed}  $criteria
     * @return Builder<Student>
     */
    public function query(User $user, array $criteria): Builder
    {
        $this->assertAllowed($user);

        foreach (['faculty_code', 'current_level', 'current_batch_code'] as $key) {
            if (blank($criteria[$key] ?? null)) {
                throw new DomainException('اختر الكلية والمستوى الحالي والعام الأكاديمي الحالي.');
            }
        }

        $recoms = array_filter((array) ($criteria['recom_code'] ?? []));

        if ($recoms === []) {
            throw new DomainException('اختر التوصية المطلوبة للترحيل.');
        }

        return $user->constrainToAcademicScope(
            Student::query()
                ->where('faculty_code', $criteria['faculty_code'])
                ->where('level_code', $criteria['current_level'])
                ->where('batch_code', $criteria['current_batch_code'])
                ->whereHas('recom', function (Builder $query) use ($recoms): void {
                    $query->whereIn('recom_code', $recoms)
                        ->whereColumn('res_recoms.level_code', 'students.level_code')
                        ->whereColumn('res_recoms.batch_code', 'students.batch_code')
                        ->where('res_recoms.semester', 2);
                })
        );
    }

    /**
     * يرحّل الطلاب داخل transaction ويسجّل العملية، ويرجع عددهم.
     *
     * @param  array<string, mixed>  $criteria
     */
    public function promote(User $user, array $criteria, mixed $newLevel, mixed $newBatch): int
    {
        if (blank($newLevel) || blank($newBatch)) {
            throw new DomainException('اختر المستوى الجديد والعام الأكاديمي الجديد.');
        }

        if ((string) $newLevel === (string) $criteria['current_level'] && (string) $newBatch === (string) $criteria['current_batch_code']) {
            throw new DomainException('الوضع الجديد مطابق للوضع الحالي.');
        }

        $query = $this->query($user, $criteria);

        return DB::transaction(function () use ($user, $criteria, $newLevel, $newBatch, $query): int {
            $ids = (clone $query)->lockForUpdate()->pluck('university_no')->all();

            if ($ids === []) {
                return 0;
            }

            Student::query()->whereIn('university_no', $ids)->update([
                'level_code' => $newLevel,
                'batch_code' => $newBatch,
                'fees8' => DB::raw('fees8 + xmod'),
            ]);

            $this->fees->refreshMany($ids);

            activity('promotion')
                ->causedBy($user)
                ->withProperties([
                    'from' => ['level_code' => $criteria['current_level'], 'batch_code' => $criteria['current_batch_code']],
                    'to' => ['level_code' => $newLevel, 'batch_code' => $newBatch],
                    'faculty_code' => $criteria['faculty_code'],
                    'recom_code' => $criteria['recom_code'],
                    'university_nos' => $ids,
                ])
                ->log('ترحيل طلاب');

            return count($ids);
        });
    }

    private function assertAllowed(User $user): void
    {
        if (! $user->can(self::PERMISSION)) {
            throw new DomainException('لا تملك صلاحية ترحيل الطلاب.');
        }
    }
}
