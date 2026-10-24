<?php

namespace App\Filament\Clusters\Reg\Resources\StudentAccounts\Pages;

use App\Filament\Clusters\Reg\Resources\StudentAccounts\StudentAccountResource;
use Filament\Resources\Pages\EditRecord;

class EditStudentAccount extends EditRecord
{
    protected static string $resource = StudentAccountResource::class;
}
