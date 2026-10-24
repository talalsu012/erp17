<?php

namespace App\Filament\Clusters\Res\Resources\Subjects\Pages;

use App\Filament\Clusters\Res\Resources\Subjects\SubjectsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSubjects extends EditRecord
{
    protected static string $resource = SubjectsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
