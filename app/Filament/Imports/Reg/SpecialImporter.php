<?php

namespace App\Filament\Imports\Reg;

use App\Models\Reg\Department;
use App\Models\Reg\Special;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SpecialImporter extends Importer
{
    protected static ?string $model = Special::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label(__('reg.code'))
                ->numeric()
                ->example('1001002')
                ->rules(['required', 'integer']),

            ImportColumn::make('department_code')
                ->label(__('reg.department'))
                // ->requiredMapping()
                // ->castStateUsing(function ($state) {
                //     $department = Department::where('name_ar', trim($state))->first();
                //     return $department?->code;
                // })
                ->rules(['required']),

            ImportColumn::make('name_ar')
                ->label(__('reg.name_ar'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('name_en')
                ->label(__('reg.name_en'))
                ->rules(['max:255']),

        ];
    }

    public function resolveRecord(): Special
    {
        return Special::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your special import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
