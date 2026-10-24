<?php

namespace App\Filament\Clusters\Invoices\Resources\Invoices\Pages;

use App\Filament\Clusters\Invoices\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
}
