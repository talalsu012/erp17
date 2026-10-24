<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * البادئة التي تميّز رقم الفاتورة عن الرقم الجامعي للطالب.
     */
    public const INVOICE_PREFIX = '100000';

    public function authorize(): bool
    {
        return true;
    }

    public function isInvoicePayment(): bool
    {
        return str_starts_with((string) $this->input('university_no'), self::INVOICE_PREFIX);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $voucherRules = ['required', 'string', 'max:255'];

        if (! $this->isInvoicePayment()) {
            $voucherRules[] = 'unique:payments,voucher_no';
        }

        return [
            'university_no' => ['required'],
            'amount' => ['required', 'numeric', 'min:1'],
            'bank' => ['required', 'string', 'max:255'],
            'voucher_no' => $voucherRules,
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'university_no.required' => 'الرقم الجامعي أو رقم الفاتورة مطلوب.',
            'amount.required' => 'المبلغ مطلوب.',
            'amount.numeric' => 'المبلغ يجب أن يكون رقماً.',
            'amount.min' => 'المبلغ يجب ألا يقل عن 1.',
            'bank.required' => 'اسم البنك مطلوب.',
            'voucher_no.required' => 'رقم القسيمة مطلوب.',
            'voucher_no.unique' => 'رقم القسيمة مسجّل مسبقاً.',
        ];
    }
}
