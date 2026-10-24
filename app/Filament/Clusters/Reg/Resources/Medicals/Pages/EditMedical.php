<?php

namespace App\Filament\Clusters\Reg\Resources\Medicals\Pages;

use App\Filament\Clusters\Reg\Resources\Medicals\MedicalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedical extends EditRecord
{
    protected static string $resource = MedicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
