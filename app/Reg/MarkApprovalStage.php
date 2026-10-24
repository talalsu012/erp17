<?php

namespace App\Reg;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MarkApprovalStage: string implements HasColor, HasIcon, HasLabel
{
    case Professor = 'professor';
    case HeadDepartment = 'head_department';
    case Dean = 'dean';
    case ScientificAffairs = 'scientific_affairs';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Professor => 'الأستاذ',
            self::HeadDepartment => 'رئيس القسم',
            self::Dean => 'العميد / الكلية',
            self::ScientificAffairs => 'الشؤون العلمية',
            self::Approved => 'معتمد نهائيًا',
            self::Rejected => 'مرفوض',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Professor => 'warning',
            self::HeadDepartment => 'info',
            self::Dean => 'info',
            self::ScientificAffairs => 'gray',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Professor => 'heroicon-m-user',
            self::HeadDepartment => 'heroicon-m-academic-cap',
            self::Dean => 'heroicon-m-building-library',
            self::ScientificAffairs => 'heroicon-m-beaker',
            self::Approved => 'heroicon-m-check-badge',
            self::Rejected => 'heroicon-m-x-circle',
        };
    }
}
