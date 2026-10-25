<?php

namespace App\Observers;

use App\Models\Student;
use App\Reg\RegAccess;
use App\Services\Reg\StudentFeeService;

/**
 * يعيد حساب رسوم الطالب عند أي تحديث لسجله (بديل المشغّل after_student_update).
 */
class StudentFeesObserver
{
    public function __construct(private readonly StudentFeeService $fees) {}

    public function creating(Student $student): void
    {
        $this->stampRegistrationAccess($student);
    }

    public function updating(Student $student): void
    {
        $this->stampRegistrationAccess($student);
        $this->fees->apply($student);
    }

    /**
     * وقت منح التسجيل الفردي: يُسجَّل عند فتح التسجيل للطالب ويُمسح عند حجبه أو إعادته للفترة.
     */
    private function stampRegistrationAccess(Student $student): void
    {
        if ($student->isDirty('reg_access')) {
            $student->reg_access_at = $student->reg_access === RegAccess::Open ? now() : null;
        }
    }
}
