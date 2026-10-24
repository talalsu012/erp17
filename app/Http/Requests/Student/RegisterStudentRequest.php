<?php

namespace App\Http\Requests\Student;

use App\Actions\Student\VerifyStudentIdentity;
use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterStudentRequest extends FormRequest
{
    private ?Student $verifiedStudent = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * الطالب الذي طابقت هويته السجل الأكاديمي (متاح بعد نجاح التحقق).
     */
    public function verifiedStudent(): Student
    {
        return $this->verifiedStudent;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'university_no' => ['required', 'string', 'max:50'],
            'nationaltyno' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users_std,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'national_id_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'identity_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'school' => ['nullable', 'string', 'max:150'],
            'percent' => ['nullable', 'numeric', 'between:0,100'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'nationality_code' => ['nullable', 'integer'],
            'state_code' => ['required', 'integer'],
            'city' => ['required', 'string', 'max:255'],
            'localty' => ['required', 'string', 'max:255'],
            'block' => ['required', 'string', 'max:255'],
            'home_no' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:255'],
            'social_media' => ['nullable', 'string', 'max:255'],
            'activites' => ['nullable', 'string', 'max:255'],
            'skill' => ['nullable', 'string', 'max:255'],
            'parent' => ['required', 'string', 'max:255'],
            'parent_alive' => ['required', 'boolean'],
            'parent_job' => ['required', 'string', 'max:255'],
            'parent_address' => ['required', 'string', 'max:255'],
            'parent_contacts' => ['required', 'string', 'max:255'],
            'mother' => ['required', 'string', 'max:255'],
            'mother_alive' => ['nullable', 'boolean'],
            'mother_job' => ['nullable', 'string', 'max:255'],
            'mother_address' => ['nullable', 'string', 'max:255'],
            'mother_contacts' => ['nullable', 'string', 'max:255'],
            'brothers' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('university_no') || $validator->errors()->has('nationaltyno')) {
                    return;
                }

                $student = app(VerifyStudentIdentity::class)->execute(
                    (string) $this->input('university_no'),
                    $this->input('nationaltyno'),
                );

                if (! $student) {
                    $validator->errors()->add('university_no', trans('student_portal.identity_mismatch'));

                    return;
                }

                $this->verifiedStudent = $student;
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.required' => trans('student_portal.photo_required'),
            'email.unique' => trans('student_portal.email_taken'),
        ];
    }
}
