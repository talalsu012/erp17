<?php

namespace App\Filament\Clusters\Reg\Resources\Specials\Pages;

use App\Filament\Clusters\Reg\Resources\Specials\SpecialsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpecials extends ListRecords
{
    protected static string $resource = SpecialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
