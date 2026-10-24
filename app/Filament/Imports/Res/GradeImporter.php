<?php

namespace App\Filament\Imports\Res;

use App\Models\Res\Grade;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class GradeImporter extends Importer
{
    protected static ?string $model = Grade::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')
                ->requiredMapping()
                ->exampleHeader('Title')
                ->label('Title (EN)')
                ->examples(['A', 'B', 'C'])
                ->rules(['required', 'max:10']),
            ImportColumn::make('titlear')
                ->exampleHeader('Title-AR')
                ->requiredMapping()
                ->label('Title (AR)')
                ->examples(['أ', 'ب', 'ج'])
                ->rules(['required', 'max:10']),
            ImportColumn::make('upper')
                ->exampleHeader('Upper-Limit')
                ->requiredMapping()
                ->label('Upper Limit')
                ->examples(['90', '80', '70'])
                ->integer()
                ->rules(['required', 'integer']),
            ImportColumn::make('lower')
                ->exampleHeader('Lower-Limit')
                ->requiredMapping()
                ->label('Lower Limit')
                ->examples(['40', '30', '20'])
                ->integer()
                ->rules(['required', 'integer']),
            ImportColumn::make('point')
                ->exampleHeader('Point')
                ->requiredMapping()
                ->label('Point')

                ->examples(['5.00'])
                ->numeric(decimalPlaces: 2)
                ->rules(['required', 'numeric']),
            ImportColumn::make('plan')
                ->exampleHeader('Plan-Code')
                ->requiredMapping()

                ->label('Plan')
                ->example('1')
                ->integer()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): Grade
    {
        return new Grade;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your grade import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
