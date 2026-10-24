<?php

namespace App\Http\Controllers\Res;

use App\Http\Controllers\Controller;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Student;
use App\Reg\AbsStatus;
use App\Reg\Semester;
use App\Reg\ShowAbsentCode;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class StudentResultsController extends Controller
{
    /**
     * الدالة الرئيسية لعرض التقرير.
     * تعمل كموجه يستدعي الدوال المتخصصة بناءً على المدخلات.
     */
    public $TestData = 9090;

    public function showResults(string $encodedData)
    {
        try {
            // 1. فك تشفير البيانات والتحقق من صحتها
            $params = $this->decodeAndPrepareParams($encodedData);

            abort_unless(auth()->user()->canAccessAcademic($params['faculty_code'], null, $params['level']), 403);

            // 2. توجيه الطلب إلى الدالة المناسبة بناءً على حالة الغياب
            $students = ((int) $params['absent'] === 2)
                ? $this->getRemedialResults($params)
                : $this->getStandardResults($params);

            // 3. جلب قائمة المواد للعرض في ترويسة الجدول
            $subjects = $this->getSubjectsForReportHeader($params, $students);

            // 4. تجهيز البيانات النهائية وإرسالها إلى الـ View
            $viewData = $this->prepareViewData($params, $students, $subjects);

            return view('filament.pages.report', $viewData);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            // التعامل مع أي أخطاء قد تحدث
            abort(400, $e->getMessage());
        }
    }

    /**
     * جلب النتائج للحالات القياسية (absent = 1).
     */
    private function getStandardResults(array $params)
    {
        $semester = (int) $params['semester'];

        $query = Student::with([
            'faculty',
            // تحميل الدرجات بناءً على الفصل الدراسي
            'marks' => function ($query) use ($semester) {
                $query->where('examType', 1); // دائمًا examType = 1 للحالات القياسية
                if ($semester === 1 || $semester === 2) {
                    $query->where('semester', $semester);
                } else { // semester = 3
                    $query->whereIn('semester', [1, 2]);
                }
            },
            // تحميل التوصيات بناءً على الفصل الدراسي
            'recom' => function ($query) use ($params, $semester) {
                $query->where(['level_code' => $params['level'], 'batch_code' => $params['batch']])
                    ->where('examType', 1);
                // لحساب الفرق، نحتاج بيانات الفصلين
                if ($semester === 2) {
                    $query->whereIn('semester', [1, 2]);
                } else {
                    $query->where('semester', $semester === 3 ? 2 : $semester);
                }
                $query->orderByDesc('semester');
            },
        ])
            ->where(['level_code' => $params['level'], 'batch_code' => $params['batch'], 'faculty_code' => $params['faculty_code']])
            ->orderBy('name_ar', 'asc');

        return $query->get()->map(function ($student) use ($semester) {
            // معالجة النتائج بعد جلبها
            $this->processStudentStandardResults($student, $semester);

            return $student;
        });
    }

    /**
     * جلب النتائج لحالات الملحق/البديل (absent = 2).
     * تبحث هذه الدالة عن الطلاب الذين كانت توصيتهم النهائية للفصل الثاني (امتحان الدور الأول)
     * هي "ملحق" أو "بديل"، ثم تعرض نتائجهم لامتحان الدور الثاني.
     */
    private function getRemedialResults(array $params)
    {
        $remedialCodes = ShowAbsentCode::getRemedialValues();

        $query = Student::with([
            'faculty',
            // جلب كل الدرجات (دور أول وثاني) لنتمكن من اختيار الأحدث للعرض
            'marks' => function ($q) {
                $q->whereIn('examType', [1, 2]);
            },
            // جلب توصيات الملحق (الدور الثاني) فقط لعرضها
            'recom' => function ($q) use ($params) {
                $q->where('level_code', $params['level'])
                    ->where('batch_code', $params['batch'])
                    ->where('examType', 2);
                // ->whereIn('recom_code', $remedialCodes);
            },
        ])
            ->where(['level_code' => $params['level'], 'batch_code' => $params['batch'], 'faculty_code' => $params['faculty_code']])
            // الشرط الأساسي: فلترة الطلاب الذين لديهم توصية "ملحق/بديل" من امتحان الدور الأول للفصل الثاني
            ->whereHas('recom', function ($q) use ($params, $remedialCodes) {
                $q->where('level_code', $params['level'])
                    ->where('batch_code', $params['batch'])
                    ->where('semester', 2)
                    ->where('examType', 1)
                    ->whereIn('recom_code', $remedialCodes);
            })
            ->orderBy('name_ar', 'asc');

        return $query->get()->map(function ($student) {
            // معالجة النتائج بعد جلبها لاختيار الدرجات الأحدث
            $this->processStudentMarks($student);

            return $student;
        });
    }

    /**
     * معالجة نتائج الطالب للحالات القياسية.
     * تقوم هذه الدالة بتصفية الدرجات والتوصيات بناءً على الفصل الدراسي المحدد.
    عند اختيار semester ==3 قم باختيار نتائج الفصل الثاني فقط وexamtype =2 فقط
     */
    private function processStudentStandardResults($student, int $semester): void
    {
        $this->processStudentMarks($student);

        $recoms = $student->recom;
        if ($recoms->isEmpty()) {
            return;
        }

        // حساب الفرق للفصل الثاني
        if ($semester === 2 && $recoms->count() > 1) {
            $sem2Recom = $recoms->firstWhere('semester', '2');
            $sem1Recom = $recoms->firstWhere('semester', '1');
            if ($sem2Recom && $sem1Recom) {
                $student->setRelation('recom', collect([$this->calculateSemesterTwoDelta($sem1Recom, $sem2Recom)]));

                return;
            }
        }

        // للحالات الأخرى، نأخذ فقط السجل الأعلى (الأحدث)
        $student->setRelation('recom', collect([$recoms->first()]));
    }

    /**
     * فلترة درجات الطالب للاحتفاظ فقط بالسجل الأحدث لكل مادة.
     */
    private function processStudentMarks($student): void
    {
        if ($student->relationLoaded('marks')) {
            $uniqueMarks = $student->marks
                ->sortByDesc('id') // الأحدث أولاً
                ->unique('subject_code'); // الاحتفاظ بالأول (الأحدث) فقط
            $student->setRelation('marks', $uniqueMarks);
        }
    }

    /**
     * حساب الفرق في الإحصائيات بين الفصل الثاني والأول.
     */
    private function calculateSemesterTwoDelta($sem1Recom, $sem2Recom)
    {
        $delta = $sem2Recom->replicate(); // ننشئ نسخة من سجل الفصل الثاني

        // حساب الفرق
        $delta->sumMarks = max(0, ($sem2Recom->sumMarks - $sem1Recom->sumMarks));
        $delta->countSubject = $sem2Recom->countSubject - $sem1Recom->countSubject;

        $delta->s_pass = $sem2Recom->s_pass - $sem1Recom->s_pass;
        $delta->s_fail = $sem2Recom->s_fail - $sem1Recom->s_fail;
        $delta->s_abs = $sem2Recom->s_abs - $sem1Recom->s_abs;

        // المعدل التراكمي للفصل الثاني هو الفرق بين مجموع النقاط مقسوماً على فرق الساعات
        $delta->gpa = ($delta->countSubject > 0) ? ($delta->sumMarks / $delta->countSubject) : 0;
        //  \dd('delta->gpa' .$delta->gpa, 'delta->sumMarks' .$delta->sumMarks, 'delta->countSubject' .$delta->countSubject, 'sem2Recom->sumMarks' .$sem2Recom->sumMarks, 'sem1Recom->sumMarks' .$sem1Recom->sumMarks, 'sem2Recom->countSubject' .$sem2Recom->countSubject, 'sem1Recom->countSubject' .$sem1Recom->countSubject);

        return $delta;
    }

    /**
     * جلب قائمة فريدة من المواد لعرضها في ترويسة التقرير.
     */
    // private function getSubjectsForReportHeader(array $params, $students)
    // {
    //     // نستخلص المواد من الطلاب الذين تم جلبهم بالفعل لتحسين الأداء
    //     return $students->flatMap(fn($student) => $student->marks)
    //         ->pluck('subject')
    //          ->filter(function ($subject) use ($params) {
    //             // إظهار المادة فقط إذا كانت على نفس المستوى والفصل والكلية للطالب
    //         //    اظهر المواد التي تكون في $params['subjects'] فقط

    //            return $subject
    //                 && $subject->level_code == $params['level']
    //                 && (
    //                     ($params['semester'] == 1 && $subject->semester_id == 1)
    //                     || ($params['semester'] == 2 && $subject->semester_id == 2)
    //                     || ($params['semester'] == 3 && in_array($subject->semester_id, [1, 2]))
    //                 )
    //                 && $subject->faculty_code == $params['faculty_code'];
    //         })
    //         ->unique('code')
    //         ->sortBy('code');
    // }

    private function getSubjectsForReportHeader(array $params, $students)
    {
        // 1. استخلاص كل المواد الفريدة من علامات الطلاب مرة واحدة
        return $students->flatMap(fn ($student) => $student->marks)
            ->pluck('subject')
            ->filter(function ($subject) use ($params) {
                // التحقق من وجود الكائن أولاً لتجنب الأخطاء
                if (! $subject) {
                    return false;
                }

                // 2. التحقق مما إذا كانت المادة ضمن قائمة المواد المختارة في الـ Params (إذا وجدت)

                if (! empty($params['subjects'])) {
                    if (! in_array($subject->code, $params['subjects'])) {
                        return false;
                    }
                }

                // 3. التحقق من تطابق الكلية والمستوى
                $isCorrectFaculty = $subject->faculty_code == $params['faculty_code'];
                $isCorrectLevel = $subject->level_code == $params['level'];

                // 4. منطق الفصل الدراسي (Semester Logic)
                $semesterId = $subject->semester_id;
                $isCorrectSemester = match ((int) $params['semester']) {
                    1 => $semesterId == 1,
                    2 => $semesterId == 2,
                    3 => in_array($semesterId, [1, 2]), // الفصل الدراسي الكامل أو السنوي
                    default => false,
                };

                return $isCorrectFaculty && $isCorrectLevel && $isCorrectSemester;
            })
            // 5. ضمان عدم تكرار المواد وترتيبها بالكود
            ->unique('code')
            ->values() // إعادة تعيين الفهارس لجعل الـ Collection نظيفة
            ->sortBy('code');
    }

    /**
     * فك تشفير البيانات والتحقق من صحتها.
     */
    private function decodeAndPrepareParams(string $encodedData): array
    {
        $data = json_decode(base64_decode($encodedData), true);

        if (! $data || ! isset($data['faculty'], $data['level'], $data['semester'], $data['batch'], $data['absent'])) {
            throw new \InvalidArgumentException('بيانات غير صالحة أو ناقصة.');
        }

        return [
            'faculty_code' => $data['faculty'],
            'level' => $data['level'],
            'semester' => $data['semester'],
            'batch' => $data['batch'],
            'absent' => $data['absent'],
            'subjects' => $data['subjects'] ?? [],
            'fields' => $data['fields'] ?? [],
            'grades' => $data['grades'] ?? [],
        ];
    }

    /**
     * تجهيز البيانات النهائية لإرسالها إلى الـ View.
     */
    private function prepareViewData(array $params, $students, $subjects): array
    {

        return [
            'students' => $students,
            'subjects' => $subjects,
            'faculty' => Faculty::find($params['faculty_code']),
            'level' => Level::find($params['level']),
            'batch' => $params['batch'],
            'semester' => $params['semester'],
            'absent' => $params['absent'],
            'semesterLabel' => Semester::from((int) $params['semester'])->getLabel(),
            'absentLabel' => AbsStatus::from((int) $params['absent'])->getLabel(),
            'fields' => $params['fields'],
        ];
    }
}
