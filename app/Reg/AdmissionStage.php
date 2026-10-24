<?php

namespace App\Reg;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * مراحل معاملة قبول الطالب بعد المعاينة. كل مرحلة اسمها الجهة المنتظَرة.
 */
enum AdmissionStage: string implements HasColor, HasLabel
{
    /** أضافه المسجل وينتظر قرار الطبيب */
    case AwaitingDoctor = 'awaiting_doctor';
    /** قرار الطبيب مدخل وينتظر توصية لجنة الكلية */
    case AwaitingCommittee = 'awaiting_committee';
    /** رفضته لجنة الكلية: المعاملة مغلقة */
    case Rejected = 'rejected';
    /** قبلته اللجنة وينتظر المسجل تحديد الرسوم */
    case AwaitingFees = 'awaiting_fees';
    /** الرسوم محددة وتظهر معلّقة للدفع في البنك (Pending) */
    case AwaitingPayment = 'awaiting_payment';
    /** دُفعت الرسوم وأُنشئ حساب الطالب، وينتظر اعتماد العميد */
    case AwaitingDean = 'awaiting_dean';
    /** اعتمده العميد: قبول نهائي */
    case Approved = 'approved';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AwaitingDoctor => 'بانتظار الطبيب',
            self::AwaitingCommittee => 'بانتظار لجنة الكلية',
            self::Rejected => 'مرفوض (مغلق)',
            self::AwaitingFees => 'بانتظار تحديد الرسوم',
            self::AwaitingPayment => 'الرسوم معلّقة للدفع',
            self::AwaitingDean => 'بانتظار اعتماد العميد',
            self::Approved => 'مقبول نهائياً',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AwaitingDoctor, self::AwaitingCommittee, self::AwaitingFees => 'warning',
            self::AwaitingPayment => 'info',
            self::AwaitingDean => 'primary',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    /** المعاملة انتهت ولا تقبل أي انتقال */
    public function isClosed(): bool
    {
        return in_array($this, [self::Rejected, self::Approved], true);
    }
}
