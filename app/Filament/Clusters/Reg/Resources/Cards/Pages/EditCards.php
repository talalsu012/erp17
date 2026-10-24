<?php

namespace App\Filament\Clusters\Reg\Resources\Cards\Pages;

use App\Filament\Clusters\Reg\Resources\Cards\CardsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCards extends EditRecord
{
    protected static string $resource = CardsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
