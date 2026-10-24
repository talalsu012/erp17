<?php

namespace App\Filament\Clusters\Res\Resources\LtRecoms\Pages;

use App\Filament\Clusters\Res\Resources\LtRecoms\LtRecomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLtRecoms extends ListRecords
{
    protected static string $resource = LtRecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
