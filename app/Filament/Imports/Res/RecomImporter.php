<?php

namespace App\Filament\Imports\Res;

use App\Models\Res\Recom;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class RecomImporter extends Importer
{
    protected static ?string $model = Recom::class;

    public static function getLabel(): string
    {
        return __('res.ImportStudentsRecoms');
    }

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('university_no')
                ->label(__('reg.university_no'))
                ->example(['6868650', '4975458', '2862811'])
                ->rules(['required', 'exists:students,university_no']),
            ImportColumn::make('faculty_code')
                ->label(__('reg.faculty'))
                ->example(['1020002', '1020002', '2020004'])
                ->rules(['required', 'exists:reg_faculties,code']),
            ImportColumn::make('level_code')
                ->label(__('reg.level'))
                ->example(['1', '2', '3'])
                ->rules(['required', 'exists:reg_levels,code']),
            ImportColumn::make('batch_code')
                ->label(__('reg.batch'))
                ->example(['2023', '2023', '2024'])
                ->rules(['required']),
            ImportColumn::make('semester')
                ->label(__('res.semester'))
                ->example(['1', '2', '1'])
                ->rules(['required', 'in:1,2']),
            ImportColumn::make('examType')
                ->label(__('res.absent'))
                ->example(['1', '2', '1'])
                ->rules(['required', 'in:1,2']),
            ImportColumn::make('recom_code')
                ->label(__('res.recom'))
                ->example(['4002', '1002', '1003'])
                ->rules(['required', 'exists:res_ltrecoms,code']),
            ImportColumn::make('gpa')
                ->label(__('res.GPA'))
                ->example(['3.5', '3.7', '3.9'])
                ->numeric()
                ->rules(['required', 'numeric', 'min:0', 'max:4']),
            ImportColumn::make('cgpa')
                ->label(__('res.CGPA'))
                ->example(['3.5', '3.7', '3.9'])
                ->numeric()
                ->rules(['required', 'numeric', 'min:0', 'max:4']),
        ];
    }

    public function resolveRecord(): Recom
    {

        return Recom::firstOrNew([
            'recom_code' => $this->data['recom_code'],
            'university_no' => $this->data['university_no'],
            'faculty_code' => $this->data['faculty_code'],
            'level_code' => $this->data['level_code'],
            'batch_code' => $this->data['batch_code'],
            'semester' => $this->data['semester'],
            'examType' => $this->data['examType'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your recom import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
