<?php

namespace App\Http\Controllers\Student;

use App\Actions\Student\StoreStudentFiles;
use App\Actions\Student\SyncStudentRecord;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ProfileUpdateRequest;
use App\Http\Requests\Student\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * الملف الشخصي للطالب. لا يتضمن حذف الحساب: يُطلب ذلك من الإدارة.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('student.profile.edit', ['user' => $request->user('student')]);
    }

    public function update(ProfileUpdateRequest $request, SyncStudentRecord $syncStudentRecord, StoreStudentFiles $storeStudentFiles): RedirectResponse
    {
        $account = $request->user('student');
        $account->fill($request->safe()->except(['photo', ...StoreStudentFiles::DOCUMENT_FIELDS]));

        if ($account->isDirty('email')) {
            $account->email_verified_at = null;
        }

        $account->save();

        if ($account->student) {
            $syncStudentRecord->execute($account->student, $request->validated());

            if ($request->hasFile('photo')) {
                $storeStudentFiles->photo($account->student, $request->file('photo'));
            }
        }

        $storeStudentFiles->replaceDocuments($account, array_filter($request->only(StoreStudentFiles::DOCUMENT_FIELDS)));

        return redirect()->route('student.profile.edit')->with('status', 'profile-updated');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user('student')->update([
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        return back()->with('status', 'password-updated');
    }
}
