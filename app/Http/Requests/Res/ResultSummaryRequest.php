<?php

namespace App\Http\Requests\Res;

use App\Helpers\Bilingual;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResultSummaryRequest extends FormRequest
{
    /**
     * صلاحية الكلية والقسم تُفحص في الكنترولر بعد التحقق من المدخلات.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'batch' => ['required'],
            'faculty' => ['required'],
            'department' => ['required'],
            'special' => ['nullable'],
            'level' => ['required'],
            'absent' => ['nullable', Rule::in([1, 2])],
            'subjects' => ['nullable', 'array'],
            'result_type' => ['nullable', Rule::in(['current', 'previous'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'batch.required' => Bilingual::msg('res_validation.batch_required'),
            'faculty.required' => Bilingual::msg('res_validation.faculty_required'),
            'department.required' => Bilingual::msg('res_validation.department_required'),
            'level.required' => Bilingual::msg('res_validation.level_required'),
            'absent.in' => Bilingual::msg('res_validation.absent_in'),
        ];
    }
}
