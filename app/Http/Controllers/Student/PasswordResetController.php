<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ForgotPasswordRequest;
use App\Http\Requests\Student\ResetPasswordRequest;
use App\Models\StudentAccount;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('student.auth.forgot-password');
    }

    /**
     * الرد واحد سواء وُجد البريد أم لا، حتى لا تُكشف الحسابات.
     */
    public function email(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::broker('students')->sendResetLink($request->only('email'));

        return back()->with('status', trans('student_portal.reset_link_sent'));
    }

    public function create(Request $request): View
    {
        return view('student.auth.reset-password', ['request' => $request]);
    }

    /**
     * @throws ValidationException
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('students')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (StudentAccount $account) use ($request): void {
                $account->forceFill([
                    'password' => Hash::make($request->string('password')->toString()),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($account));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [trans('student_portal.reset_failed')]]);
        }

        return redirect()->route('student.login')->with('status', trans('student_portal.reset_done'));
    }
}
