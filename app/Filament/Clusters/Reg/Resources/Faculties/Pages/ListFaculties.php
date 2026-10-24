<?php

namespace App\Filament\Clusters\Reg\Resources\Faculties\Pages;

use App\Filament\Clusters\Reg\Resources\Faculties\FacultiesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFaculties extends ListRecords
{
    protected static string $resource = FacultiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
