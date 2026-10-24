<?php

namespace App\Http\Controllers\Student;

use App\Actions\Student\RegisterStudentAccount;
use App\Actions\Student\VerifyStudentIdentity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\RegisterStudentRequest;
use App\Http\Requests\Student\VerifyRegistrationRequest;
use App\Models\Reg\LtColumn;
use App\Reg\LtGroup;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        $states = LtColumn::query()
            ->where('grouped', LtGroup::State->value)
            ->orderBy('name_ar')
            ->pluck('name_ar', 'code');

        return view('student.auth.register', compact('states'));
    }

    /**
     * خطوة تمهيدية للواجهة: تعرض اسم الطالب وكليته للتأكد منهما، والتحقق النهائي في store().
     */
    public function verify(VerifyRegistrationRequest $request, VerifyStudentIdentity $verifyStudentIdentity): JsonResponse
    {
        $student = $verifyStudentIdentity->execute(
            $request->string('university_no')->toString(),
            $request->input('nationaltyno'),
        );

        if (! $student) {
            return response()->json(['valid' => false, 'message' => trans('student_portal.identity_mismatch')], 422);
        }

        return response()->json([
            'valid' => true,
            'name' => $student->name_ar,
            'name_en' => $student->name_en,
            'university_no' => $student->university_no,
            'faculty' => $student->faculty?->name_ar,
            'department' => $student->department?->name_ar,
            'level' => $student->level?->name_ar,
        ]);
    }

    public function store(RegisterStudentRequest $request, RegisterStudentAccount $registerStudentAccount): RedirectResponse
    {
        $account = $registerStudentAccount->execute(
            $request->verifiedStudent(),
            $request->safe()->except(['photo', ...RegisterStudentAccount::DOCUMENT_FIELDS]),
            $request->file('photo'),
            array_filter($request->only(RegisterStudentAccount::DOCUMENT_FIELDS)),
        );

        event(new Registered($account));
        Auth::guard('student')->login($account);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard');
    }
}
