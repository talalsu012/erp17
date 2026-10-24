<?php

namespace App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource\Pages;

use App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCertStudent extends EditRecord
{
    protected static string $resource = CertStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
