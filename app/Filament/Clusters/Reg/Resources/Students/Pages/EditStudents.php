<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Pages;

use App\Actions\Students\FillEnglishName;
use App\Filament\Clusters\Reg\Resources\Students\StudentsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditStudents extends EditRecord
{
    protected static string $resource = StudentsResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(FillEnglishName::class)->execute($data);
    }

    // after update student, refresh the page
    protected function afterSave(): void
    {
        $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
