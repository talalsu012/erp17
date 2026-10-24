<?php

namespace App\Filament\Clusters\Res\Resources\LtRecoms\Pages;

use App\Filament\Clusters\Res\Resources\LtRecoms\LtRecomResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLtRecom extends EditRecord
{
    protected static string $resource = LtRecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
