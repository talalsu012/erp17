<?php

namespace App\Filament\Exports\Res;

use App\Models\Res\Mark;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class MarkExporter extends Exporter
{
    protected static ?string $model = Mark::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('university_no')
                ->label(__('reg.university_no')),
            ExportColumn::make('student.name_ar')
                ->label(__('reg.student')),
            ExportColumn::make('subject_code')
                ->label(__('res.code')),
            ExportColumn::make('subject.name_ar')
                ->label(__('res.subject')),
            ExportColumn::make('faculty.name_ar')
                ->label(__('reg.faculty')),
            ExportColumn::make('level.name_ar')
                ->label(__('reg.level')),
            ExportColumn::make('batch.name_ar')
                ->label(__('reg.batch')),
            ExportColumn::make('semester')
                ->label(__('res.semester')),
            ExportColumn::make('mark')
                ->label(__('res.mark')),
            ExportColumn::make('cwork')
                ->label(__('res.cwork')),
            ExportColumn::make('grade')
                ->label(__('res.grade')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your mark export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
