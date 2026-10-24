<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'university_no' => ['required', 'string', 'max:50'],
            'nationaltyno' => ['nullable', 'string', 'max:30'],
        ];
    }
}
