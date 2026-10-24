<?php

namespace App\Filament\Clusters\Reg\Resources\Levels\Pages;

use App\Filament\Clusters\Reg\Resources\Levels\LevelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLevel extends EditRecord
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
