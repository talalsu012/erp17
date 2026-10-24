<?php

namespace App\Reg;

enum MarkApprovalStatus: string
{
    case Draft = 'draft';
    case Department = 'department';
    case Dean = 'dean';
    case Academic = 'academic';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة (الأستاذ)',
            self::Department => 'قيد مراجعة القسم',
            self::Dean => 'قيد مراجعة العميد',
            self::Academic => 'قيد مراجعة الشؤون العلمية',
            self::Approved => 'معتمد نهائيًا',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Department => 'warning',
            self::Dean => 'info',
            self::Academic => 'primary',
            self::Approved => 'success',
        };
    }
}
