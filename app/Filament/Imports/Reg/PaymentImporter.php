<?php

namespace App\Filament\Imports\Reg;

use App\Models\Reg\Payment;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PaymentImporter extends Importer
{
    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        return [
            // university_no, amount, bankdate, voucher_no, batch_code,bank
            ImportColumn::make('university_no')
                ->label(__('reg.university_no'))
                ->numeric()
                ->rules(['required', 'integer'])
                ->requiredMapping()
                ->example('20230001'),
            ImportColumn::make('amount')
                ->label(__('reg.amount'))
                ->numeric()
                ->example('1000')
                ->rules(['required']),
            ImportColumn::make('bankdate')
                ->label(__('reg.bankdate'))
                ->example('2023-01-01')
                ->rules(['required', 'date']),
            ImportColumn::make('voucher_no')
                ->label(__('reg.voucher_no'))
                ->example('VOC-001')
                ->rules(['required', 'string']),
            ImportColumn::make('batch_code')
                ->label(__('reg.batch_code'))
                ->example('2023')
                ->rules(['required', 'string']),
            ImportColumn::make('bank')
                ->label(__('reg.bank'))
                ->example('FIB')
                ->rules(['required', 'string']),
        ];
    }

    public function resolveRecord(): Payment
    {
        return Payment::firstOrNew([
            'voucher_no' => $this->data['voucher_no'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your payment import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
