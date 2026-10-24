<?php

namespace App\Filament\Imports\Reg;

use App\Models\Reg\Faculty;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class FacultyImporter extends Importer
{
    protected static ?string $model = Faculty::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label(__('reg.code'))
                ->numeric()
                ->rules(['required', 'integer'])
                ->requiredMapping()
                ->example('1'),
            ImportColumn::make('campus_code')
                ->label(__('reg.campus'))
                ->numeric()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('name_ar')
                ->label(__('reg.name_ar'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name_en')
                ->label(__('reg.name_en'))
                ->rules(['nullable', 'max:255']),

        ];
    }

    public function resolveRecord(): Faculty
    {
        return Faculty::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {

        $body = 'Your faculty import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
