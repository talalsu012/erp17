<?php

namespace App\Filament\Clusters\Cert\Resources\Cert\CertSettingResource\Pages;

use App\Filament\Clusters\Cert\Resources\Cert\CertSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCertSetting extends EditRecord
{
    protected static string $resource = CertSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
