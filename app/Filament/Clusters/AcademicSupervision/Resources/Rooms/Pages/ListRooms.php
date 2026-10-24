<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Pages;

use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\RoomsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
