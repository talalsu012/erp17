<?php

namespace App\Filament\Clusters\Res\Resources\SubjectApprovals\Pages;

use App\Filament\Clusters\Res\Resources\SubjectApprovals\SubjectApprovalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubjectApprovals extends ListRecords
{
    protected static string $resource = SubjectApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
