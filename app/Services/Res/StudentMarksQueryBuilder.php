<?php

namespace App\Services\Res;

use App\Models\Res\Subject;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class StudentMarksQueryBuilder
{
    public function __construct(protected MarkEntryFilters $filters, protected MarkEntryAuthorization $auth) {}

    public function build(array $data): Builder
    {
        // إذا كان أي فلتر فارغ → إرجاع استعلام فارغ
        if (! $this->filters->allRequiredFiltersPresent($data)) {
            return Student::query()->whereRaw('0 = 1');
        }

        $subject = Subject::query()->find($data['subject']);

        if (! $subject || (string) $subject->faculty_code !== (string) $data['faculty'] || (string) $subject->level_code !== (string) $data['level']) {
            return Student::query()->whereRaw('0 = 1');
        }

        $query = $subject->constrainStudents(Student::query())
            ->with($this->getEagerLoads($data))
            ->where('faculty_code', $data['faculty'])
            ->when($data['department'], fn ($q) => $q->where('department_code', $data['department']))
            ->when($data['special'], fn ($q) => $q->where('special_code', $data['special']))
            ->where('level_code', $data['level'])
            ->where('batch_code', $data['batch']);

        // فلترة حسب نوع الغياب (الملحق)
        $this->filters->applyAbsentFilter($query, $data['absent']);

        // التحقق من صلاحيات المرحلة (Workflow)
        // $this->auth->applyStagePermissionFilter($query, $data['subject'], $data['batch']);

        return $query;
    }

    protected function getEagerLoads(array $data): array
    {
        return [
            'faculty',
            'department',
            'special',
            'batch',
            'grouping.room',
            // إضافة التحميل المسبق للدرجات مع شروط الفلترة
            'marks' => fn ($q) => $q->where([
                ['subject_code', $data['subject']],
                ['batch_code', $data['batch']],
                ['level_code', $data['level']],
                ['examType', $data['absent']],
            ]),
            'marks.subject',
            'marks.reason',
        ];
    }

    public function count(array $data): int
    {
        return $this->build($data)->count();
    }
}
