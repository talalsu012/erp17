<?php

namespace App\Filament\Clusters\Reg\Resources\OpenRegs\Pages;

use App\Filament\Clusters\Reg\Resources\OpenRegs\OpenRegResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpenReg extends EditRecord
{
    protected static string $resource = OpenRegResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
