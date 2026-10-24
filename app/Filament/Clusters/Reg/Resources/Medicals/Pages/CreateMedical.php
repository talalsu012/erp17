<?php

namespace App\Filament\Clusters\Reg\Resources\Medicals\Pages;

use App\Filament\Clusters\Reg\Resources\Medicals\MedicalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedical extends CreateRecord
{
    protected static string $resource = MedicalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['decided_by'] = auth()->id();

        return $data;
    }
}
