<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsDashboardRequest extends FormRequest
{
    /**
     * البيانات تُقيَّد بنطاق المستخدم الأكاديمي داخل الخدمة، فيكفي أن يكون مسجلاً.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'batch' => ['nullable', 'integer'],
            'faculty' => ['nullable', 'integer'],
            'department' => ['nullable', 'integer'],
            'level' => ['nullable', 'integer'],
            'exam' => ['nullable', Rule::in([1, 2])],
        ];
    }
}
