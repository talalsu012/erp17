<?php

namespace App\Reg;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

// Assuming Filament is installed and you're using its contracts/traits for convenience.
// If not using Filament, you'd implement label() and icon() methods manually.

enum Semester: int implements HasColor, HasIcon, HasLabel
{
    case First = 1;
    case Second = 2;
    case Summer = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::First => 'الفصل الدراسي الأول',
            self::Second => 'الفصل الدراسي الثاني',
            self::Summer => 'الفصل الأول و الثاني',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::First => 'heroicon-o-calendar-days', // Example icon
            self::Second => 'heroicon-o-book-open',    // Example icon
            self::Summer => 'heroicon-o-academic-cap',  // Example icon
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::First => 'info',    // Example color (e.g., blue in Filament)
            self::Second => 'success', // Example color (e.g., green in Filament)
            self::Summer => 'warning',  // Example color (e.g., yellow in Filament)
        };
    }
}
