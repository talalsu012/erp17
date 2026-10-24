<?php

namespace App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource\Pages;

use App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCertStudents extends ListRecords
{
    protected static string $resource = CertStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\Cert\CertStudentImporter::class)
              // ->visible(fn () => auth()->user()?->hasRole('super_admin'))
               // ->import_request_variables(['file'])
                ->label('استيراد طلاب الشهادات')
                ->modalHeading('استيراد طلاب الشهادات من ملف')
                ->modalWidth('lg')
                ->color('success'),
        ];
    }
}
