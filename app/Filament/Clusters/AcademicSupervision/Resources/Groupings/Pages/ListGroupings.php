<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Groupings\Pages;

use App\Filament\Clusters\AcademicSupervision\Resources\Groupings\GroupingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGroupings extends ListRecords
{
    protected static string $resource = GroupingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
