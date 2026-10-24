<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Pages;

use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\RoomsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRooms extends EditRecord
{
    protected static string $resource = RoomsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
