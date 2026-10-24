<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Reg\Faculty;
use App\Services\CertificateService;
use App\Services\CertificateStyleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateService $certificateService
    ) {}

    /**
     * Generate main certificate view
     */
    public function generate(Request $request, string $slug)
    {
        $certificate = $this->certificateService->getCertificateForView($slug);

        $signatories = $this->getSignatoryGroups(
            $certificate->faculty_code
        );

        $locale = $request->query('lang', 'en');
        app()->setLocale($locale);

        $styles = CertificateStyleService::getStyles('graduation');
        $css = CertificateStyleService::css($styles, $locale);

        return View::make('cert.template', [
            'certificate' => $certificate,
            'signatories' => $signatories,
            'today' => now()->locale($locale)->isoFormat('MMMM Do, YYYY'),
            'lang' => $locale,
            'alumiaDate' => $this->certificateService->formatAlumiaDate($certificate->alumia_date, $locale),
            'hijriDate' => $this->certificateService->formatHijriDate($certificate->hijri_date),
            'verifyUrl' => url("/verify/{$certificate->verify_hash}"),
            'css' => $css,
        ]);
    }

    /**
     * Generate academic transcript (HTML view - PDF commented)
     */
    public function generate_details(string $universityNo, string $lang = 'ar')
    {
        $transcriptData = $this->certificateService->generateTranscript($universityNo, $lang);

        $signatories = $this->getSignatoryGroups(
            $transcriptData['student']['faculty_code']
        );
        $transcriptData['signatories'] = $signatories;

        $styles = CertificateStyleService::getStyles('transcript');
        $css = CertificateStyleService::cssTranscript($styles, $lang);
        $transcriptData['css'] = $css;

        if (isset($transcriptData['error'])) {
            return response()->json(['error' => $transcriptData['error']], 404);
        }

        return view('cert.transcript', compact('transcriptData'));
    }

    /**
     * Print result page
     */
    public function PrintResult(Request $request, array $data)
    {
        return view('print-result', ['data' => $data]);
    }

    /**
     * Generate affidavit view
     */
    public function affidavit(Request $request, string $slug)
    {
        $certificate = $this->certificateService->getCertificateForView($slug);

        $signatories = $this->getSignatoryGroups(
            $certificate->faculty_code
        );

        $locale = $request->query('lang', 'en');
        app()->setLocale($locale);

        $alumiaDate = $certificate->alumia_date;

        return View::make('cert.affidavit', [
            'certificate' => $certificate,
            'signatories' => $signatories,
            'today' => now()->locale($locale)->isoFormat('MMMM Do, YYYY'),
            'lang' => $locale,
            'alumiaDate' => $this->certificateService->formatAlumiaDate($alumiaDate, $locale),
            'hijriDate' => $this->certificateService->formatHijriDate($certificate->hijri_date),
            'affidavit_date_ar' => $alumiaDate?->locale('ar')->translatedFormat('F Y'),
            'affidavit_date_en' => $alumiaDate?->locale('en')->translatedFormat('F Y'),
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    //  Private Helper Methods
    // ────────────────────────────────────────────────────────────────

    private function getSignatoryGroups(
        int|string $facultyCode
    ): array {
        $faculty = Faculty::query()
            ->select([
                'code',
                'name_ar',
                'name_en',
                'primary_hex',
                'metadata',
            ])
            ->where('code', $facultyCode)
            ->first();

        if (! $faculty) {
            return [
                'faculty' => null,

                'dean_name' => null,
                'dean_name_en' => null,

                'registrar_name' => null,
                'registrar_name_en' => null,

                'scientific_affairs_name' => null,
                'scientific_affairs_name_en' => null,
            ];
        }

        $metadata = $faculty->metadata ?? [];

        return [
            'faculty' => $faculty,

            'dean_name' => $metadata['dean_name'] ?? null,

            'dean_name_en' => $metadata['dean_name_en'] ?? null,

            'registrar_name' => $metadata['registrar_name'] ?? null,

            'registrar_name_en' => $metadata['registrar_name_en'] ?? null,

            'scientific_affairs_name' => $metadata['scientific_affairs_name'] ?? null,

            'scientific_affairs_name_en' => $metadata['scientific_affairs_name_en'] ?? null,
        ];
    }
}
