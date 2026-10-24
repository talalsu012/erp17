<?php

namespace App\Filament\Clusters\Res\Resources\Resons\Pages;

use App\Filament\Clusters\Res\Resources\Resons\ResonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResons extends ListRecords
{
    protected static string $resource = ResonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
