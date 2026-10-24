<?php

namespace App\Reg;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum InvoiceStatus: string implements HasColor, HasIcon, HasLabel
{
    case pending = 'pending';
    case processing = 'processing';
    case completed = 'completed';
    case failed = 'failed';
    case refunded = 'refunded';
    case disputed = 'disputed';
    case cancelled = 'cancelled';
    case paid = 'paid';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::pending => 'قيد الانتظار',
            self::processing => 'قيد المعالجة',
            self::completed => 'مكتملة',
            self::failed => 'فشلت',
            self::refunded => 'مسترجعة',
            self::disputed => 'متنازع عليها',
            self::cancelled => 'ملغاة',
            self::paid => 'مدفوعة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::pending => 'gray',
            self::processing => 'warning',
            self::completed => 'success',
            self::failed => 'danger',
            self::refunded => 'info',
            self::disputed => 'warning',
            self::cancelled => 'danger',
            self::paid => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::pending => 'heroicon-m-clock',
            self::processing => 'heroicon-m-arrow-path',
            self::completed => 'heroicon-m-check-circle',
            self::failed => 'heroicon-m-x-circle',
            self::refunded => 'heroicon-m-arrow-uturn-left',
            self::disputed => 'heroicon-m-exclamation-triangle',
            self::cancelled => 'heroicon-m-x-mark',
            self::paid => 'heroicon-m-banknotes',
        };
    }
}
