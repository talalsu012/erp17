<?php

namespace App\Http\Controllers\Res;

use App\Http\Controllers\Controller;
use App\Models\Student;

class TranscriptController extends Controller
{
    public function printTranscript($university_no)
    {
        // 1. جلب الطالب مع جميع العلاقات الضرورية لتجنب مشكلة N+1
        $student = Student::with([
            'faculty',
            'department',
            'special',
            'batch',
            'academicYear',
            'marks.subject',
            'marks.level',
            'recom.recomName',
            'recom.level',
        ])->findOrFail($university_no);

        // الرابط يُفتح بالعنوان مباشرة، فيُفحص نطاق المستخدم (كلية وقسم الطالب) في الخادم
        abort_unless(auth()->user()->canAccessAcademic($student->faculty_code, $student->department_code), 403);

        // 2. تجميع العلامات (Marks) بناءً على رمز المستوى (level_code) ثم الفصل الدراسي (examType)
        $groupedMarks = $student->marks->groupBy('level_code')->map(function ($levelMarks) {
            return $levelMarks->groupBy('examType'); // تجميع داخلي بالفصل الدراسي
        })->sortKeys(); // ترتيب المستويات تصاعدياً

        // 3. تجميع التوصيات والقرارات بناءً على المستوى الدراسي
        $groupedRecoms = $student->recom->groupBy('level_code');

        return view('Res.transcript', compact('student', 'groupedMarks', 'groupedRecoms'));
    }
}
