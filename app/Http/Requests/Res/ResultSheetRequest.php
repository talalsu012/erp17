<?php

namespace App\Http\Requests\Res;

use App\Helpers\Bilingual;
use App\Http\Controllers\Res\DashBoardResultController;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResultSheetRequest extends FormRequest
{
    /**
     * صلاحية الكلية تُفحص في الكنترولر بعد التحقق من المدخلات.
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
            'department' => ['nullable'],
            'special' => ['nullable'],
            'level' => ['required'],
            'absent' => ['required', Rule::in([1, 2])],
            'subjects' => ['required', 'array', 'min:1'],
            'fields' => ['required', 'array', 'min:1'],
            'result_type' => ['nullable', Rule::in(['current', 'previous'])],
            'show_history' => ['nullable', 'boolean'],
            'design' => ['nullable', Rule::in(array_keys(DashBoardResultController::DESIGNS))],
            'per_page' => ['nullable', 'integer', 'min:4', 'max:40'],
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
            'level.required' => Bilingual::msg('res_validation.level_required'),
            'absent.required' => Bilingual::msg('res_validation.absent_required'),
            'absent.in' => Bilingual::msg('res_validation.absent_in'),
            'subjects.required' => Bilingual::msg('res_validation.subjects_required'),
            'subjects.min' => Bilingual::msg('res_validation.subjects_min'),
            'fields.required' => Bilingual::msg('res_validation.fields_required'),
            'fields.min' => Bilingual::msg('res_validation.fields_min'),
            'per_page.min' => Bilingual::msg('res_validation.per_page_min'),
            'per_page.max' => Bilingual::msg('res_validation.per_page_max'),
        ];
    }
}
