<?php

namespace App\Filament\Exports\Reg;

use App\Models\Reg\Faculty;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class FacultyExporter extends Exporter
{
    protected static ?string $model = Faculty::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')->label(__('reg.code')),
            ExportColumn::make('campus.name_ar')->label(__('reg.campus')),
            ExportColumn::make('name_ar')->label(__('reg.name_ar')),
            ExportColumn::make('name_en')->label(__('reg.name_en')),
            ExportColumn::make('is_visible')->label(__('reg.is_visible')),
            ExportColumn::make('start_reg')->label(__('reg.start_date')),
            ExportColumn::make('end_reg')->label(__('reg.end_date')),
            ExportColumn::make('results_published')->label(__('reg.results_published')),
            ExportColumn::make('created_at')->label(__('reg.created_at')),
            ExportColumn::make('updated_at')->label(__('reg.updated_at')),
            ExportColumn::make('deleted_at')->label(__('reg.deleted_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your faculty export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
