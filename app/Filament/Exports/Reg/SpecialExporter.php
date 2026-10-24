<?php

namespace App\Filament\Exports\Reg;

use App\Models\Reg\Special;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class SpecialExporter extends Exporter
{
    protected static ?string $model = Special::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')->label(__('reg.code')),
            ExportColumn::make('department.name_ar')->label(__('reg.department')),
            ExportColumn::make('name_ar')->label(__('reg.name_ar')),
            ExportColumn::make('name_en')->label(__('reg.name_en')),
            ExportColumn::make('is_visible')->label(__('reg.is_visible')),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your special export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
