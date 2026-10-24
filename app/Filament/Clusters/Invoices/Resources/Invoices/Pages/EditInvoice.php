<?php

namespace App\Filament\Clusters\Invoices\Resources\Invoices\Pages;

use App\Filament\Clusters\Invoices\Resources\Invoices\InvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        if ($this->record->bank_pay == 1) {
            return [];
        }

        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        // إذا كانت الفاتورة مدفوعة، لا تظهر أزرار الحفظ (Save & Cancel)
        if ($this->record->bank_pay == 1) {
            return [];
        }

        return parent::getFormActions();
    }
}
