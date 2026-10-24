<?php

namespace App\Filament\Clusters\Res\Resources\SubjectApprovals\Pages;

use App\Filament\Clusters\Res\Resources\SubjectApprovals\SubjectApprovalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSubjectApproval extends EditRecord
{
    protected static string $resource = SubjectApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
