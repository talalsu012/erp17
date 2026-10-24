<?php

namespace App\Filament\Clusters\Res\Resources\Recoms\Pages;

use App\Filament\Clusters\Res\Resources\Recoms\RecomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecoms extends ListRecords
{
    protected static string $resource = RecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
