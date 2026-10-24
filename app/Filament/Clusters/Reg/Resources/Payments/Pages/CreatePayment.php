<?php

namespace App\Filament\Clusters\Reg\Resources\Payments\Pages;

use App\Filament\Clusters\Reg\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
