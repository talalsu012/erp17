<?php

namespace App\Actions\Students;

use App\Models\Student;
use App\Models\StudentAccount;

/**
 * يحذف حساب بوابة الطالب فقط.
 * حقل students.student_account هو «براءة الذمة المالية» (شرط عرض النتائج) وليس حساب البوابة،
 * لذلك لا يُمسّ هنا.
 */
class ResetStudentAccount
{
    /**
     * @return bool false عند عدم وجود حساب لهذا الطالب
     */
    public function execute(Student $student): bool
    {
        $deleted = StudentAccount::query()
            ->where('university_no', $student->university_no)
            ->delete();

        return $deleted > 0;
    }
}
