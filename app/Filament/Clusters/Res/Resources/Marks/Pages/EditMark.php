<?php

namespace App\Filament\Clusters\Res\Resources\Marks\Pages;

use App\Filament\Clusters\Res\Resources\Marks\MarkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMark extends EditRecord
{
    protected static string $resource = MarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
