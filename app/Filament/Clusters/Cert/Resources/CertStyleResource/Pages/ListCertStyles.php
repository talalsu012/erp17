<?php

namespace App\Filament\Clusters\Cert\Resources\CertStyleResource\Pages;

use App\Filament\Clusters\Cert\Resources\CertStyleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCertStyles extends ListRecords
{
    protected static string $resource = CertStyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
