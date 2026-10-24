<?php

namespace App\Notifications\Student;

use Illuminate\Auth\Notifications\ResetPassword;

/**
 * رسالة استعادة كلمة مرور الطالب برابط يشير إلى بوابة الطالب لا إلى لوحة الإدارة.
 */
class ResetPasswordNotification extends ResetPassword
{
    /**
     * @param  mixed  $notifiable
     */
    protected function resetUrl($notifiable): string
    {
        return url(route('student.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
