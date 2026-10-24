<?php

namespace App\Filament\Clusters\Reg\Resources\StudentAccounts\Pages;

use App\Filament\Clusters\Reg\Resources\StudentAccounts\StudentAccountResource;
use Filament\Resources\Pages\ListRecords;

class ListStudentAccounts extends ListRecords
{
    protected static string $resource = StudentAccountResource::class;
}
