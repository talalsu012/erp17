<?php

namespace App\Actions\Student;

use App\Models\StudentAccount;
use Illuminate\Support\Facades\Password;

/**
 * يرسل رابط إعادة تعيين كلمة المرور إلى بريد حساب الطالب عبر broker «students».
 */
class SendStudentResetLink
{
    public const PLACEHOLDER_DOMAIN = '@students.local';

    /**
     * البريد الوهمي الذي تنشئه إدارة القبول لا يستقبل رسائل، فيجب تصحيحه قبل الإرسال.
     */
    public function hasDeliverableEmail(StudentAccount $account): bool
    {
        return filled($account->email) && ! str_ends_with((string) $account->email, self::PLACEHOLDER_DOMAIN);
    }

    public function execute(StudentAccount $account): bool
    {
        if (! $this->hasDeliverableEmail($account)) {
            return false;
        }

        return Password::broker('students')->sendResetLink(['email' => $account->email]) === Password::RESET_LINK_SENT;
    }
}
