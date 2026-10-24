<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Pages;

use App\Filament\Clusters\AcademicSupervision\Resources\Lectures\LectureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLectures extends ListRecords
{
    protected static string $resource = LectureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
