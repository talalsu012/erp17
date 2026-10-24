<?php

namespace App\Filament\Imports\Res;

use App\Models\Res\Reson;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ResonImporter extends Importer
{
    protected static ?string $model = Reson::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->requiredMapping()
                ->label(__('res.code'))
                ->rules(['required', 'integer', 'unique:res_resons,code']),
            ImportColumn::make('name_ar')
                ->requiredMapping()
                ->label(__('reg.name_ar'))
                ->rules(['required', 'max:100']),
            ImportColumn::make('name_en')
                ->label(__('reg.name_en')),
            ImportColumn::make('description')
                ->label('الوصف'),
            ImportColumn::make('mark')
                ->label('الدرجة')
                ->numeric()
                ->rules(['nullable', 'integer', 'between:0,100']),
            ImportColumn::make('grade')
                ->label('التقدير')
                ->rules(['nullable', 'max:20']),
            ImportColumn::make('point')
                ->label('النقاط')
                ->numeric()
                ->rules(['nullable', 'numeric', 'between:0,99.99']),
            ImportColumn::make('recom_code')
                ->label('رمز القرار')
                ->numeric()
                ->rules(['nullable', 'integer', 'exists:res_ltrecoms,code']),
        ];
    }

    public function resolveRecord(): Reson
    {
        return Reson::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your reson import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
