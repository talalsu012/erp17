<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Pages;

use App\Actions\Students\FillEnglishName;
use App\Filament\Clusters\Reg\Resources\Students\StudentsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudents extends CreateRecord
{
    protected static string $resource = StudentsResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(FillEnglishName::class)->execute($data);
    }
}
