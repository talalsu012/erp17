<?php

namespace App\Reg;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * حالة الطالب في المقرر حسب نسبة الغياب (لائحة 2020: المادة 5 للدبلوم والمادة 8 للبكالريوس).
 */
enum AbsenceStatus: string implements HasColor, HasLabel
{
    /** دون حد الإنذار */
    case Normal = 'normal';
    /** بلغ 15% فأكثر: إنذار على لوحة الإعلانات */
    case Warning = 'warning';
    /** تجاوز 25%: مقترح للحرمان بانتظار قرار مجلس الكلية */
    case DeprivationProposed = 'deprivation_proposed';
    /** حرمان معتمد بقرار مجلس الكلية (نهائي) */
    case Deprived = 'deprived';
    /** رُفع الحرمان بقرار المجلس (استثناء) */
    case Lifted = 'lifted';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Normal => 'منتظم',
            self::Warning => 'إنذار غياب',
            self::DeprivationProposed => 'مقترح للحرمان',
            self::Deprived => 'محروم',
            self::Lifted => 'رُفع الحرمان',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Normal => 'success',
            self::Warning => 'warning',
            self::DeprivationProposed => 'danger',
            self::Deprived => 'danger',
            self::Lifted => 'info',
        };
    }

    /** الحالات التي يقرّرها المجلس ولا تُعاد كتابتها آلياً عند إعادة الحساب */
    public function isDecided(): bool
    {
        return in_array($this, [self::Deprived, self::Lifted], true);
    }
}
