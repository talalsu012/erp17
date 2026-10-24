<?php

namespace App\Filament\Exports\Res;

use App\Models\Res\Recom;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class RecomExporter extends Exporter
{
    protected static ?string $model = Recom::class;

    public static function getColumns(): array
    {
        return [
            // Relationships
            ExportColumn::make('student.university_no')
                ->label(__('reg.university_no')),

            ExportColumn::make('student.name_ar')
                ->label(__('reg.name_ar')),

            ExportColumn::make('level.name_ar')
                ->label(__('reg.level')),

            ExportColumn::make('batch.name_ar')
                ->label(__('reg.batch')),

            ExportColumn::make('recomName.name_ar')
                ->label(__('res.recom')),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your recom export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
