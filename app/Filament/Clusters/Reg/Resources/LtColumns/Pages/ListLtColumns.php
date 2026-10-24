<?php

namespace App\Filament\Clusters\Reg\Resources\LtColumns\Pages;

use App\Filament\Clusters\Reg\Resources\LtColumns\LtColumnResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLtColumns extends ListRecords
{
    protected static string $resource = LtColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
