<?php

namespace App\Actions\Student;

use App\Models\Student;
use App\Models\StudentAccount;
use Illuminate\Support\Str;

/**
 * يتحقق أن الرقم الجامعي يطابق سجلاً أكاديمياً لا يملك حساباً في البوابة.
 * الرقم الوطني اختياري، فإن أُدخل وجب أن يطابق السجل.
 * يرجع null عند أي عدم تطابق دون بيان السبب.
 */
class VerifyStudentIdentity
{
    public function execute(string $universityNo, ?string $nationalId = null): ?Student
    {
        $student = Student::query()->where('university_no', trim($universityNo))->first();

        if (! $student) {
            return null;
        }

        if (filled($nationalId) && ! hash_equals($this->normalize((string) $student->nationaltyno), $this->normalize($nationalId))) {
            return null;
        }

        if (StudentAccount::query()->where('university_no', $student->university_no)->exists()) {
            return null;
        }

        return $student;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->replaceMatches('/\s+/', '')->lower()->toString();
    }
}
