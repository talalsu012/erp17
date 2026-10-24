<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Pages;

use App\Filament\Clusters\AcademicSupervision\Resources\Lectures\LectureResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLecture extends CreateRecord
{
    protected static string $resource = LectureResource::class;
}
