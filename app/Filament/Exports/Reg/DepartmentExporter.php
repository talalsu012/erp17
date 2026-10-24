<?php

namespace App\Filament\Exports\Reg;

use App\Models\Reg\Department;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class DepartmentExporter extends Exporter
{
    protected static ?string $model = Department::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')->label(__('reg.code')),
            ExportColumn::make('faculty.name_ar')->label(__('reg.faculty')),
            ExportColumn::make('name_ar')->label(__('reg.name_ar')),
            ExportColumn::make('name_en')->label(__('reg.name_en')),
            ExportColumn::make('is_visible')->label(__('reg.is_visible')),
            ExportColumn::make('results_published')->label(__('reg.results_published')),
            ExportColumn::make('deleted_at')->label(__('reg.deleted_at')),
            ExportColumn::make('created_at')->label(__('reg.created_at')),
            ExportColumn::make('updated_at')->label(__('reg.updated_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your department export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
