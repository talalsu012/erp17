<?php

namespace App\Reg;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * تخصيص فردي لحالة تسجيل الطالب. الحقل الفارغ يعني «حسب فترة open-regs».
 *
 * - Open: التسجيل مفتوح للطالب حتى لو أُغلقت الفترة (ويُعتدّ بمدفوعاته من تاريخ المنح).
 * - Closed: التسجيل محجوب عن الطالب حتى لو كانت الفترة مفتوحة.
 */
enum RegAccess: string implements HasColor, HasLabel
{
    case Open = 'open';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'مفتوح للطالب',
            self::Closed => 'محجوب عن الطالب',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Closed => 'danger',
        };
    }
}
