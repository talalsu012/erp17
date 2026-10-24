<?php

namespace App\Http\Requests\Student;

use App\Models\StudentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * الاسم يُؤخذ من السجل الأكاديمي ولا يعدّله الطالب.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(StudentAccount::class)->ignore($this->user('student')->id),
            ],
            'mobile' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'localty' => ['nullable', 'string', 'max:255'],
            'block' => ['nullable', 'string', 'max:255'],
            'home_no' => ['nullable', 'string', 'max:255'],
            'social_media' => ['nullable', 'string', 'max:255'],
            'parent' => ['nullable', 'string', 'max:255'],
            'parent_alive' => ['nullable', 'boolean'],
            'parent_job' => ['nullable', 'string', 'max:255'],
            'parent_address' => ['nullable', 'string', 'max:255'],
            'parent_contacts' => ['nullable', 'string', 'max:255'],
            'mother' => ['nullable', 'string', 'max:255'],
            'mother_alive' => ['nullable', 'boolean'],
            'mother_job' => ['nullable', 'string', 'max:255'],
            'mother_address' => ['nullable', 'string', 'max:255'],
            'mother_contacts' => ['nullable', 'string', 'max:255'],
            'brothers' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'national_id_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'identity_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ];
    }
}
