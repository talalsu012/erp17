<?php

namespace App\Filament\Clusters\Res\Resources\Recoms\Pages;

use App\Filament\Clusters\Res\Resources\Recoms\RecomResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRecom extends EditRecord
{
    protected static string $resource = RecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
