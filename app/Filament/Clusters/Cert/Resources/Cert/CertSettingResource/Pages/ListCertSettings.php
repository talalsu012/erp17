<?php

namespace App\Filament\Clusters\Cert\Resources\Cert\CertSettingResource\Pages;

use App\Filament\Clusters\Cert\Resources\Cert\CertSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCertSettings extends ListRecords
{
    protected static string $resource = CertSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
