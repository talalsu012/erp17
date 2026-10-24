<?php

namespace App\Filament\Imports\Res;

use App\Models\Res\Mark;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class MarkImporter extends Importer
{
    protected static ?string $model = Mark::class;

    public static function getLabel(): string
    {
        return __('res.ImportStudentsMarks');
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('university_no')
                ->label(__('reg.university_no'))
                ->requiredMapping()
                ->example(['20210001', '20210002', '20210003'])
                ->rules(['required', 'exists:students,university_no']),
            ImportColumn::make('subject_code')
                ->requiredMapping()
                ->label(__('res.code'))
                ->example(['45101', '33101', '67101'])
                ->rules(['required', 'exists:subjects,code']),
            ImportColumn::make('batch_code')
                ->requiredMapping()
                ->label(__('reg.batch_code'))
                ->example(['2022', '2023', '2024'])
                ->rules(['required']),
            ImportColumn::make('examType')
                ->requiredMapping()
                ->label(__('res.examType'))
                ->example(['1', '2'])
                ->rules(['required']),
            ImportColumn::make('faculty_code')
                ->requiredMapping()
                ->label(__('reg.faculty_code'))
                ->example(['1001', '1002', '1003'])
                ->rules(['required']),

            ImportColumn::make('department_code')
                ->requiredMapping()
                ->label(__('reg.department_code'))
                ->example(['1001', '1002', '1003'])
                ->rules(['required']),

            ImportColumn::make('special_code')
                ->label(__('reg.special_code'))
                ->example(['1001', '1002', '1003'])
            // ->rules(['required'])
            ,

            ImportColumn::make('level_code')
                ->requiredMapping()
                ->label(__('reg.level_code'))
                ->example(['1001', '1002', '1003'])
                ->rules(['required']),
            ImportColumn::make('semester')
                ->label(__('res.semester'))
                ->example(['1', '2'])
                ->rules(['required', 'in:1,2']),
            ImportColumn::make('mark')
                ->label(__('res.mark'))
                ->example(['85', '90', '75'])
                ->numeric()
                ->rules(['required', 'numeric', 'between:0,100']),
            ImportColumn::make('cwork')
                ->label(__('res.cwork'))
                ->example(['10', '15', '20'])
                ->numeric()
                ->rules(['numeric', 'between:0,30']),
            // 🔴 تم إزالة required لأننا سنقوم بحسابها تلقائياً
            ImportColumn::make('grade')
                ->requiredMapping()
                ->label(__('res.grade'))
                ->example(['A', 'B+', 'C'])
                ->rules(['max:20']),

            // 🔴 تم إزالة required/numeric صارم للسماح بحسابها تلقائياً
            ImportColumn::make('point')
                ->label(__('res.sum'))
                ->example(['4.0', '3.5', '2.0'])
                ->rules(['numeric', 'nullable']),

            ImportColumn::make('reson_code')
                ->label(__('res.reson_code'))
                ->example(['1', '2', '3'])
                ->rules(['nullable', 'max:20']),

        ];
    }

    public function resolveRecord(): Mark
    {
        $isMarkNumeric = isset($this->data['mark']) && is_numeric($this->data['mark']);
        $resonCode = $this->data['reson_code'] ?? null;

        // تخطي العملية فقط إذا كانت الدرجة غير رقمية والسبب هو 16
        if ($isMarkNumeric && $resonCode == 16) {

            // استخراج درجة الامتحان كعدد صحيح
            $tot_exam = (int) ($this->data['mark'] ?? 0);
            $points = 0.0;
            $grade = 'F';

            // تطبيق دالة حساب النقاط والتقديرات
            if ($tot_exam >= 80 && $tot_exam <= 100) {
                $points = 4.0;
                $grade = 'A';
            } elseif ($tot_exam >= 75 && $tot_exam <= 79) {
                $points = 3.5;
                $grade = 'B+';
            } elseif ($tot_exam >= 70 && $tot_exam <= 74) {
                $points = 3.0;
                $grade = 'B';
            } elseif ($tot_exam >= 60 && $tot_exam <= 69) {
                $points = 2.5;
                $grade = 'C+';
            } elseif ($tot_exam >= 50 && $tot_exam <= 59) {
                $points = 2.0;
                $grade = 'C';
            } elseif ($tot_exam >= 0 && $tot_exam < 50) {
                $points = 0.0;
                $grade = 'F';
            }

            // حقن القيم المحسوبة في مصفوفة البيانات ليقوم المُستورد (Importer) بتخزينها
            $this->data['point'] = $points;
            $this->data['grade'] = $grade;

        }

        return Mark::firstOrNew([
            'university_no' => $this->data['university_no'],
            'subject_code' => $this->data['subject_code'],
            'batch_code' => $this->data['batch_code'],
            'examType' => $this->data['examType'],

        ]);

    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your mark import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
