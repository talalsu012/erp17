<?php

namespace App\Filament\Clusters\Reg\Resources\Faculties\Pages;

use App\Filament\Clusters\Reg\Resources\Faculties\FacultiesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditFaculties extends EditRecord
{
    protected static string $resource = FacultiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
