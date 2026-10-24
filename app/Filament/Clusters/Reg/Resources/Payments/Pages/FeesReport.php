<?php

namespace App\Filament\Clusters\Reg\Resources\Payments\Pages;

use App\Filament\Clusters\Reg\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class FeesReport extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PaymentResource::class;

    protected string $view = 'filament.clusters.reg.resources.payments.pages.fees-report';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
