<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\TranscriptGenerator;
use App\Models\Cert\CertStudent;
use Carbon\CarbonInterface;

class CertificateService
{
    public function getCertificateForView(string $id): CertStudent
    {
        return CertStudent::query()
            ->where('is_visible', true)
            ->with(['degree', 'grade', 'faculty', 'admission'])
            ->findOrFail($id);
    }

    public function generateTranscript(string $universityNo, string $lang = 'ar'): array
    {
        // Move TranscriptGenerator logic here or keep delegation
        // Recommended: move most of the logic from TranscriptGenerator here
        // or create TranscriptService

        return app(TranscriptGenerator::class)->generateTranscript($universityNo, $lang);
    }

    /**
     * Format Gregorian date (Alumia) in Arabic & English
     */
    public function formatAlumiaDate(?CarbonInterface $date, string $locale = 'en'): ?array
    {
        if (! $date) {
            return null;
        }

        $monthsAr = [
            1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
        ];

        $daysAr = [
            1 => 'الأول', 'الثاني', 'الثالث', 'الرابع', 'الخامس', 'السادس',
            'السابع', 'الثامن', 'التاسع', 'العاشر', 'الحادي عشر', 'الثاني عشر',
            'الثالث عشر', 'الرابع عشر', 'الخامس عشر', 'السادس عشر', 'السابع عشر',
            'الثامن عشر', 'التاسع عشر', 'العشرون', 'الحادي والعشرون',
            'الثاني والعشرون', 'الثالث والعشرون', 'الرابع والعشرون',
            'الخامس والعشرون', 'السادس والعشرون', 'السابع والعشرون',
            'الثامن والعشرون', 'التاسع والعشرون', 'الثلاثون', 'الحادي والثلاثون',
        ];

        $dayNum = (int) $date->format('j');
        $result = [
            'ar' => "الموافق {$daysAr[$dayNum]} من شهر {$monthsAr[$date->month]} سنة {$date->year} م",
            'en' => $date->locale('en')->isoFormat('Do [of] MMMM YYYY'), // 16th of August 2025
            'en_html' => $this->formatEnglishDateWithSup($date),
        ];

        return $result;
    }

    private function formatEnglishDateWithSup(CarbonInterface $date): string
    {
        $day = $date->day;
        $suffix = $this->getOrdinalSuffix($day);

        return "on the {$day}<sup class=\"text-xs align-super\">{$suffix}</sup> of {$date->monthName} {$date->year}";
    }

    private function getOrdinalSuffix(int $number): string
    {
        if (in_array($number % 100, [11, 12, 13])) {
            return 'th';
        }

        return match ($number % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th'
        };
    }

    /**
     * Format Hijri date (can be improved further)
     */
    public function formatHijriDate(?string $hijriString): ?array
    {
        if (! $hijriString) {
            return null;
        }

        $pattern = '/(\d{1,2})\s+(.+?)\s*،\s*(\d{4})/';
        if (! preg_match($pattern, $hijriString, $matches)) {
            return null;
        }

        $day = (int) $matches[1];
        $monthAr = trim($matches[2]);
        $year = $matches[3];

        // ... rest of your hijri logic here ...

        return [
            'ar' => '...',
            'en' => '...',
            'en_html' => '...',
        ];
    }
}
