<?php

namespace App\Http\Controllers\Res;

use App\Http\Controllers\Controller;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Res\LtRecom;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Reg\Semester;
use App\Reg\ShowAbsentCode;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class PrintSummaryController extends Controller
{
    public function showSmmarySubjects(string $encodedData)
    {
        try {
            // 1. فك تشفير البيانات والتحقق من صحتها
            $params = $this->decodeAndPrepareParams($encodedData);

            abort_unless(auth()->user()->canAccessAcademic($params['faculty'], null, $params['level']), 403);

            // 2. جلب جميع طلاب الدفعة والمستوى المحددين
            $students = $this->getStudents($params);

            // 3. جلب قائمة المواد للعرض
            $subjects = $this->getSubjectsForReport($params);

            // 4. حساب الإحصائيات المطلوبة
            $statistics = $this->calculateStatistics($params, $students);

            // 5. تجهيز البيانات النهائية وإرسالها إلى الـ View
            $viewData = $this->prepareViewData($params, $students, $subjects, $statistics);

            return view('filament.pages.result-summary', $viewData);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * جلب جميع طلاب الدفعة والمستوى المحددين
     */
    private function getStudents(array $params)
    {
        return Student::with([
            'faculty',
            'level',
            'batch',
            'marks',
            'recom' => function ($query) use ($params) {
                $query->where('level_code', $params['level'])
                    ->where('batch_code', $params['batch']);
            },
        ])
            ->where('level_code', $params['level'])
            ->where('batch_code', $params['batch'])
            ->where('faculty_code', $params['faculty'])

            ->when($params['examType'] == 2, function ($query) use ($params) {
                $query->whereHas('recom', function ($recomQuery) use ($params) {
                    $recomQuery
                        ->whereIn('recom_code', ShowAbsentCode::getRemedialValues())
                        ->where('examType', 1)
                        ->where('semester', Semester::Second->value)
                        ->where('level_code', $params['level'])
                        ->where('batch_code', $params['batch']);
                });
            })

            ->orderBy('name_ar', 'asc')
            ->get();
    }

    private function getSubjectsForReport(array $params)
    {
        return Subject::where('level_code', $params['level'])
            ->where('faculty_code', $params['faculty'])
            ->orderBy('code')
            ->get();
    }

    private function calculateStatistics(array $params, $students): array
    {
        // 1. العدد الكلي للطلاب
        $totalStudents = $students->count();

        // 2. الطلاب الجالسين فعلياً للامتحان (ناس البدائل المسطرة كاملة ما معاهم)
        $excludedRecomCodes = $this->getExcludedRecomCodes();
        $sittingStudents = $this->getSittingStudents($params, $students, $excludedRecomCodes);
        $actualSittingCount = $sittingStudents->count();

        // 3. نسبة النجاح من الجالسين
        $passedStudentsCount = $this->getPassedStudentsCount($params, $sittingStudents);
        $passPercentage = ($actualSittingCount > 0) ? ($passedStudentsCount / $actualSittingCount) * 100 : 0;

        // 4. إحصاء لكل توصية
        $recommendationStats = $this->getRecommendationStatistics($params, $students, $excludedRecomCodes);

        // 5. نسبة نجاح كل مادة من الجالسين فعلياً
        $subjectPassRates = $this->getSubjectPassRates($params, $sittingStudents);

        return [
            'totalStudents' => $totalStudents,
            'actualSittingCount' => $actualSittingCount,
            'passPercentage' => round($passPercentage, 2),
            'recommendationStats' => $recommendationStats,
            'subjectPassRates' => $subjectPassRates,
        ];
    }

    private function getExcludedRecomCodes(): array
    {
        return LtRecom::where('code', 3001)
            ->pluck('code')
            ->toArray();
    }

    /**
     * الحصول على الطلاب الجالسين فعلياً (باستثناء البدائل المسطرة)
     */
    private function getSittingStudents(array $params, $students, array $excludedRecomCodes)
    {
        // 1. جلب أحدث توصية لكل طالب (الدور الثاني مقدم على الأول)
        $latestRecoms = Recom::where('batch_code', $params['batch'])
            ->where('level_code', $params['level'])
            ->where('faculty_code', $params['faculty'])
            ->where('semester', 2)
            ->where('examType', $params['examType'])
            ->orderByDesc('examType')
            ->orderByDesc('semester')
            ->orderByDesc('id')
            ->get()
            ->unique('university_no');

        // 2. تحديد أرقام الطلاب الذين حالتهم النهائية "بديل مسطر"
        $excludedStudentNos = $latestRecoms->whereIn('recom_code', $excludedRecomCodes)
            ->pluck('university_no')
            ->toArray();

        // 3. فلترة الطلاب: الجالس هو من ليس في قائمة المستبعدين
        // وأيضاً (اختياري) من لديه درجة واحدة على الأقل في سجل الدرجات
        return $students->filter(function ($student) use ($excludedStudentNos) {
            $isExcluded = in_array($student->university_no, $excludedStudentNos);

            // إذا أردت دقة مطلقة: التأكد أن لديه درجات فعلية وليس غياباً في كل المواد
            $hasAnyMark = $student->marks->count() > 0;

            return ! $isExcluded && $hasAnyMark;
        });
    }

    /**
     * الحصول على عدد الطلاب الناجحين من الجالسين
     */
    private function getPassedStudentsCount(array $params, $sittingStudents): int
    {
        // كود التوصية "ناجح في كل المقررات" - يرجى تعديله حسب نظامك
        $passedAllRecomCode = ['1001', '1002', '1003', '1004'];

        $passedStudents = Recom::where('batch_code', $params['batch'])
            ->where('level_code', $params['level'])
            ->whereIn('recom_code', $passedAllRecomCode)
            ->where('semester', 2)
            ->where('examType', $params['examType'])
            ->whereIn('university_no', $sittingStudents->pluck('university_no'))
            ->count();

        return $passedStudents;
    }

    /**
     * إحصاء تفصيلي للطلاب حسب التوصيات
     */
    private function getRecommendationStatistics(array $params, $students, array $excludedRecomCodes): array
    {
        // 1. جلب التوصيات ككائنات تحتوي على (الكود والاسم)
        $recomDefinitions = LtRecom::all();

        $statistics = [];

        foreach ($recomDefinitions as $recom) {
            // 2. حساب العدد لكل كود
            $count = Recom::where('batch_code', $params['batch'])
                ->where('level_code', $params['level'])
                ->where('faculty_code', $params['faculty']) // تأكد من دقة الكلية
                ->where('recom_code', $recom->code)
                ->where('semester', 2)
                ->where('examType', $params['examType'])
                ->whereIn('university_no', $students->pluck('university_no'))
                ->count();

            // 3. التخزين باستخدام الاسم (name_ar) كمفتاح بشرط أن يكون العدد أكبر من صفر
            if ($count > 0) {
                $statistics[$recom->name] = $count;
            }
        }

        return $statistics;
    }

    /**
     * حساب نسبة نجاح كل مادة من الجالسين فعلياً
     */
    private function getSubjectPassRates(array $params, $sittingStudents): array
    {
        // الحصول على جميع مواد المستوى
        $subjects = Subject::where('level_code', $params['level'])
            ->where('faculty_code', $params['faculty'])
            ->get();

        $sittingStudentIds = $sittingStudents->pluck('university_no')->toArray();
        $subjectPassRates = [];

        foreach ($subjects as $subject) {
            // عدد الطلاب الجالسين للمادة (الذين لديهم درجة لهذه المادة)
            $sittingCount = Mark::where('subject_code', $subject->code)
                ->where('batch_code', $params['batch'])
                ->where('examType', $params['examType'])
                ->whereIn('university_no', $sittingStudentIds)
                ->count();

            // عدد الطلاب الناجحين في المادة (العلامة >= 50)
            $passedCount = Mark::where('subject_code', $subject->code)
                ->where('batch_code', $params['batch'])
                ->where('examType', $params['examType'])
                ->whereIn('university_no', $sittingStudentIds)
                ->where(function ($query) {
                    $query->where('mark', '>=', 50) // الشرط العام للنجاح
                        ->orWhere(function ($q) {
                            $q->where('mark', '>=', 40)
                                ->whereHas('student', function ($studentQuery) {
                                    // استخدام stady_code من علاقة الطالب
                                    $studentQuery->where('stady_code', '20060002');
                                });
                        });
                })
                ->count();

            $passRate = ($sittingCount > 0) ? ($passedCount / $sittingCount) * 100 : 0;

            $subjectPassRates[] = [
                'subject_code' => $subject->code,
                'subject_name' => $subject->name_ar,
                'sitting_count' => $sittingCount,
                'passed_count' => $passedCount,
                'pass_rate' => round($passRate, 2),
            ];
        }

        return $subjectPassRates;
    }

    /**
     * فك تشفير البيانات والتحقق من صحتها
     */
    private function decodeAndPrepareParams(string $encodedData): array
    {
        $data = json_decode(base64_decode($encodedData), true);

        if (! $data || ! isset($data['faculty'], $data['level'], $data['batch'])) {
            throw new \InvalidArgumentException('بيانات غير صالحة أو ناقصة.');
        }

        return [
            'faculty' => $data['faculty'],
            'level' => $data['level'],
            'batch' => $data['batch'],
            'examType' => $data['examType'],
        ];
    }

    /**
     * تجهيز البيانات النهائية لإرسالها إلى الـ View
     */
    private function prepareViewData(array $params, $students, $subjects, array $statistics): array
    {
        return [
            'students' => $students,
            'subjects' => $subjects,
            'faculty' => Faculty::find($params['faculty']),
            'level' => Level::find($params['level']),
            'batch' => $params['batch'],
            'statistics' => $statistics,
        ];
    }
}
