<?php

namespace App\Filament\Clusters\Reg\Resources\Campuses\Pages;

use App\Filament\Clusters\Reg\Resources\Campuses\CampusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampuses extends ListRecords
{
    protected static string $resource = CampusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
