<?php

namespace App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource\Pages;

use App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource;
use App\Models\Cert\CertStudent;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use Filament\Resources\Pages\Page;

class ViewCertificate extends Page
{
    protected static string $resource = CertStudentResource::class;

    protected string $view = 'filament.resources.cert-student-resource.pages.view-certificate';

    public CertStudent $record;

    public $marksByYear;

    public $recommendations;

    public function mount(int|string $record): void
    {
        $this->record = CertStudent::findOrFail($record);
        $this->loadStudentData();
    }

    protected function loadStudentData(): void
    {
        // Fetch marks and group them by academic year (assuming batch relates to year)
        // This logic might need adjustment based on your exact schema for linking marks to years
        $this->marksByYear = Mark::where('university_no', $this->record->universityno)
            ->with('subject', 'batch') // Eager load relationships
            ->get()
            ->groupBy('batch.name'); // Group by batch name (e.g., "2019/2020")

        // Fetch recommendations
        $this->recommendations = Recom::where('university_no', $this->record->universityno)
            ->with('recomName', 'level', 'batch') // Eager load relationships
            ->get();
    }
}
