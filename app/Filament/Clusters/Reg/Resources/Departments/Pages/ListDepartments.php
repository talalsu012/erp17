<?php

namespace App\Filament\Clusters\Reg\Resources\Departments\Pages;

use App\Filament\Clusters\Reg\Resources\Departments\DepartmentsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
