<?php

namespace App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource\Pages;

use App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCertStudent extends ViewRecord
{
    protected static string $resource = CertStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
