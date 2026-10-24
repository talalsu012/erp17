<?php

namespace App\Filament\Clusters\Reg\Resources\LtColumns\Pages;

use App\Filament\Clusters\Reg\Resources\LtColumns\LtColumnResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLtColumn extends EditRecord
{
    protected static string $resource = LtColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
