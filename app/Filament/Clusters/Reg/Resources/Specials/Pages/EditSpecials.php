<?php

namespace App\Filament\Clusters\Reg\Resources\Specials\Pages;

use App\Filament\Clusters\Reg\Resources\Specials\SpecialsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSpecials extends EditRecord
{
    protected static string $resource = SpecialsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
