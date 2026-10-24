<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Groupings\Pages;

use App\Filament\Clusters\AcademicSupervision\Resources\Groupings\GroupingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGrouping extends EditRecord
{
    protected static string $resource = GroupingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
