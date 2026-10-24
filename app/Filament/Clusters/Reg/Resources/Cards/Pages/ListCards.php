<?php

namespace App\Filament\Clusters\Reg\Resources\Cards\Pages;

use App\Filament\Clusters\Reg\Resources\Cards\CardsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCards extends ListRecords
{
    protected static string $resource = CardsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
