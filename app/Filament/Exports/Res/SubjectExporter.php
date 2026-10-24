<?php

namespace App\Filament\Exports\Res;

use App\Models\Res\Subject;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class SubjectExporter extends Exporter
{
    protected static ?string $model = Subject::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('faculty.name_ar')->label(__('reg.faculty')),
            ExportColumn::make('department.name_ar')->label(__('reg.department')),
            ExportColumn::make('special.name_ar')->label(__('reg.special')),
            ExportColumn::make('level.name_ar')->label(__('reg.level')),
            ExportColumn::make('semester_id')->label(__('res.semester')),
            ExportColumn::make('parent.name_ar')->label(__('res.parent_subject')),
            ExportColumn::make('code')->label(__('res.code')),
            ExportColumn::make('pin')->label(__('res.pin')),
            ExportColumn::make('name_ar')->label(__('reg.name_ar')),
            ExportColumn::make('name_en')->label(__('reg.name_en')),
            ExportColumn::make('crch')->label(__('res.crch')),
            ExportColumn::make('exam')->label(__('res.exam')),
            ExportColumn::make('plans.name')->label(__('res.plan')),
            ExportColumn::make('status')->label(__('res.status')),
            ExportColumn::make('types.name')->label(__('res.type')),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your subject export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
