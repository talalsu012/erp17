<?php

namespace App\Filament\Clusters\Res\Resources\Marks\Pages;

use App\Filament\Clusters\Res\Resources\Marks\MarkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarks extends ListRecords
{
    protected static string $resource = MarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
