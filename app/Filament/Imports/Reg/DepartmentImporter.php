<?php

namespace App\Filament\Imports\Reg;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class DepartmentImporter extends Importer
{
    protected static ?string $model = Department::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->numeric()
                ->example('1001002')
                ->rules(['required', 'integer']),

            ImportColumn::make('faculty_code')
                ->label('Faculty')
                ->requiredMapping()
                ->castStateUsing(function ($state) {

                    $faculty = Faculty::where('name_ar', trim($state))->first();

                    return $faculty?->code;

                })
                ->rules(['required']),

            ImportColumn::make('name_ar')
                ->requiredMapping()
                ->example('قسم علوم الحاسوب')
                ->rules(['required', 'max:255']),
            ImportColumn::make('name_en')
                ->example('Computer Science Department')
                ->rules(['nullable', 'max:255']),
        ];
    }

    public function resolveRecord(): Department
    {
        return Department::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your department import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
