<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Cert\CertStudent;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Student;
use App\Models\StudentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * لوحة الطالب. كل الاستعلامات مقيّدة بالرقم الجامعي لحساب الطالب المسجَّل دخوله،
 * ولا تقرأ أي معرّف من الطلب.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var StudentAccount $account */
        $account = $request->user('student');
        $student = $this->resolveStudent($account->university_no);

        $canViewResults = $student?->hasClearedAccount() ?? false;

        return view('student.dashboard', [
            'user' => $account,
            'student' => $student,
            'photoUrl' => $account->avatar_url ? route('student.photo') : asset('students/avatar/avatar.jpg'),
            'canViewResults' => $canViewResults,
            'terms' => $student && $canViewResults ? $this->buildTerms($student) : collect(),
            'payments' => $student?->payments ?? collect(),
            'attendance' => $student ? $this->buildAttendance($student) : collect(),
        ]);
    }

    private function resolveStudent(string|int|null $universityNo): ?Student
    {
        if (! $universityNo) {
            return null;
        }

        $relations = [
            'faculty.campus',
            'department',
            'special',
            'level',
            'batch',
            'academicYear',
            'admission',
            'stady',
            'status',
            'x_status',
            'nationality',
            'gender',
            'state',
            'installment',
            'discountPercent',
            'medicalFitness',
            'card',
            'payments' => fn ($query) => $query
                ->with(['batch', 'level'])
                ->orderByDesc('bankdate')
                ->orderByDesc('id'),
        ];

        if (Schema::hasTable((new CertStudent)->getTable())) {
            $relations[] = 'certificate';
        }

        return Student::with($relations)->where('university_no', $universityNo)->first();
    }

    /**
     * تجميع الدرجات والتوصيات لكل فصل دراسي (مستوى / دفعة / فصل / دور).
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildTerms(Student $student): Collection
    {
        $marks = Mark::query()
            ->with(['subject', 'level', 'batch'])
            ->where('university_no', $student->university_no)
            ->where('is_visible', true)
            ->get();

        $recoms = Recom::query()
            ->with(['recomName', 'level', 'batch'])
            ->where('university_no', $student->university_no)
            ->where('is_visible', true)
            ->get();

        $termKey = static fn ($row): string => implode('|', [
            $row->level_code,
            $row->batch_code,
            $row->getRawOriginal('semester'),
            $row->getRawOriginal('examType'),
        ]);

        $marksByTerm = $marks->groupBy($termKey);
        $recomsByTerm = $recoms->keyBy($termKey);

        return $marksByTerm->keys()
            ->merge($recomsByTerm->keys())
            ->unique()
            ->map(function (string $key) use ($marksByTerm, $recomsByTerm): array {
                $termMarks = $marksByTerm->get($key, collect());
                $recom = $recomsByTerm->get($key);
                $source = $termMarks->first() ?? $recom;

                return [
                    'level' => $source->level?->name_ar,
                    'batch' => $source->batch?->name_ar,
                    'semester' => (int) $source->getRawOriginal('semester'),
                    'exam_type' => (int) $source->getRawOriginal('examType'),
                    'marks' => $termMarks->sortBy('subject_code')->values(),
                    'recom' => $recom,
                    'sort' => [
                        (int) $source->level_code,
                        (int) $source->batch_code,
                        (int) $source->getRawOriginal('semester'),
                        (int) $source->getRawOriginal('examType'),
                    ],
                ];
            })
            ->sortBy('sort')
            ->values();
    }

    /**
     * ملخص الحضور لكل مقرر.
     *
     * @return Collection<int, array{subject: ?string, present: int, absent: int, late: int, total: int}>
     */
    private function buildAttendance(Student $student): Collection
    {
        return $student->lectures()
            ->with('subject')
            ->get()
            ->groupBy('subject_code')
            ->map(function (Collection $lectures): array {
                $statuses = $lectures->pluck('pivot.status');

                return [
                    'subject' => $lectures->first()->subject?->name_ar,
                    'present' => $statuses->filter(fn ($status) => $status === 'present')->count(),
                    'absent' => $statuses->filter(fn ($status) => $status === 'absent')->count(),
                    'late' => $statuses->filter(fn ($status) => $status === 'late')->count(),
                    'total' => $lectures->count(),
                ];
            })
            ->values();
    }
}
