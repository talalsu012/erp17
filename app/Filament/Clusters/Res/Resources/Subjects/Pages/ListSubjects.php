<?php

namespace App\Filament\Clusters\Res\Resources\Subjects\Pages;

use App\Filament\Clusters\Res\Resources\Subjects\SubjectsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
