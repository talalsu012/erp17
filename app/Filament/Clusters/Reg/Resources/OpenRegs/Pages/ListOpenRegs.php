<?php

namespace App\Filament\Clusters\Reg\Resources\OpenRegs\Pages;

use App\Filament\Clusters\Reg\Resources\OpenRegs\OpenRegResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpenRegs extends ListRecords
{
    protected static string $resource = OpenRegResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
