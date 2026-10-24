<?php

namespace App\Filament\Clusters\Res\Resources\Resons\Pages;

use App\Filament\Clusters\Res\Resources\Resons\ResonResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditReson extends EditRecord
{
    protected static string $resource = ResonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
