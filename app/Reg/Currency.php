<?php

namespace App\Reg;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Currency: string implements HasColor, HasIcon, HasLabel
{
    case SDG = 'SDG';
    case USD = 'USD';
    case SAR = 'SAR';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SDG => 'info',
            self::USD => 'warning',
            self::SAR => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SDG => 'heroicon-o-pencil',
            self::USD => 'heroicon-o-clock',
            self::SAR => 'heroicon-o-check-circle',
        };
    }
}
