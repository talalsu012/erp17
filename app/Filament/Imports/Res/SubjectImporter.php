<?php

namespace App\Filament\Imports\Res;

use App\Models\Res\Subject;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SubjectImporter extends Importer
{
    protected static ?string $model = Subject::class;

    public static function getColumns(): array
    {

        return [
            ImportColumn::make('faculty_code')
                ->example(['1001', '1002', '1003'])
                ->label(__('reg.faculty_code'))
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('department_code')
                ->example(['2001', '2002', '2003'])
                ->label(__('reg.department_code'))
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('special_code')
                ->example(['300101', '300201', '300301'])
                ->label(__('reg.special_code'))
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('level_code')
                ->example(['1001', '1002', '1003'])
                ->label(__('reg.level_code'))
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('semester_id')
                ->example(['1', '2'])
                ->label(__('res.semester'))
                ->numeric()
                ->rules(['integer']),

            ImportColumn::make('code')
                ->example(['13001', '13002', '13003'])

                ->requiredMapping()
                ->label(__('res.code'))
                ->rules(['required', 'max:255']),

            ImportColumn::make('pin')
                ->example(['CNT13001', 'CNT13002', 'CNT13003'])
                ->label(__('res.pin'))
                ->requiredMapping()
                ->rules(['max:255']),
            ImportColumn::make('name_ar')
                ->example(['مقدمة في الحاسوب', 'الرياضيات 1', 'اللغة الإنجليزية 1'])
                ->label(__('res.name_ar'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name_en')
                ->example(['Introduction to Computer', 'Mathematics 1', 'English Language 1'])
                ->label(__('res.name_en'))
                ->rules(['max:255']),
            ImportColumn::make('crch')
                ->label(__('res.crch'))
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('exam')
                ->example(['70', '100', '50'])
                ->label(__('res.exam'))
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('plan')
                ->example(['1001001', '1001002', '1001003'])
                ->label(__('res.plan'))
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('status')
                ->example(['1', '0'])
                ->label(__('res.status'))
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('type')
                ->example(['1000', '2000', '3000'])
                ->label(__('res.type'))
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),

        ];
    }

    public function resolveRecord(): Subject
    {
        return Subject::firstOrNew([
            'code' => $this->data['code'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your subject import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
