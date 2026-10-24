<?php

namespace App\Filament\Imports\Cert;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Models\Cert\CertStudent;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class CertStudentImporter extends Importer
{
    protected static ?string $model = CertStudent::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('universityno')
                ->label(__('reg.university_no'))
                ->example('100001023')
                ->rules(['max:255']),
            ImportColumn::make('name_ar')
                ->label(__('reg.name_ar'))
                ->example('أحمد محمد')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            // ImportColumn::make('slug')
            //     ->fillRecordUsing(function (CertStudent $record, string $state): void {
            //         $record->slug = Str::slug($state);
            //     })
            //     ->rules(['required', 'max:255']),
            ImportColumn::make('name_en')
                ->label(__('reg.name_en'))
                ->example('Ahmed Mohamed')
                ->rules(['max:255']),
            ImportColumn::make('gender_code')
                ->label(__('reg.gender'))
                ->example('10010001')
                ->numeric()
                ->requiredMapping()
                ->rules(['integer']),
            ImportColumn::make('alumia_date')
                ->label(__('reg.alumia_date'))
                // ->format('Y-m-d')
                ->example('2023-09-01')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('hijri_date')
                ->example('الأربعاء ، 12 جمادى الآخر ، 1447')
                ->label('Hijri Date')
                ->rules(['max:255']),
            ImportColumn::make('admission_code')
                ->label(__('reg.admission'))
                ->example('10')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('academic_year_code')
                ->label(__('reg.academic_year'))
                ->example('2023')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('stady_code')
                ->label(__('reg.stady'))
                ->example('20060001')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('degree')
                ->label(__('reg.degree'))
                ->example('2')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('grade')
                ->label(__('reg.grade'))
                ->example('6')
                ->requiredMapping()
                ->relationship(),
            ImportColumn::make('faculty_code')
                ->label(__('reg.faculty_code'))
                ->example('19')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            // ImportColumn::make('department_code')
            //     ->label(__('reg.department_code'))
            //     ->rules(['max:255']),
            // ImportColumn::make('special_code')
            //     ->label(__('reg.special_code'))
            //     ->numeric()
            //     ->rules(['integer']),
            ImportColumn::make('nationalty_code')
                ->label(__('reg.nationality'))
                ->example('10020004')
                ->numeric()
                ->requiredMapping()
                ->rules(['integer']),
            ImportColumn::make('meeting_no')
                ->label(__('reg.meeting_no'))
                ->example('2023-002')
                ->requiredMapping()
                ->rules(['max:255']),
            // ImportColumn::make('remarks')
            //     ->rules(['max:255']),
            // ImportColumn::make('remarks_en')
            //     ->rules(['max:255']),
        ];
    }

    public function mutateRowData(array $data): array
    {
        $name = $data['name_ar'] ?? '';

        $slug = trim(
            preg_replace(
                '/-+/',
                '-',
                preg_replace('/[^ء-يa-zA-Z0-9]+/u', '-', $name)
            ),
            '-'
        );

        $data['slug'] = $slug;
        $data['hijri_date'] = Hijri::MediumDate($data['alumia_date'] ?? '');

        logger($data); // بدل dd

        return $data;
    }

    public function resolveRecord(): ?CertStudent
    {
        // return CertStudent::firstOrNew([
        //     'slug' => Str::slug($this->data['name_ar'] ?? ''),
        // ]);

        return new CertStudent;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your cert student import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
