<?php

namespace App\Services;

use Carbon\Carbon;

class DateFormatter
{
    public function alumia(Carbon $date): array
    {
        return [
            'ar' => $this->arabicDate($date),
            'en' => $this->englishOrdinal($date),
        ];
    }

    private function englishOrdinal(Carbon $date): string
    {
        $day = $date->day;
        $suffix = match (true) {
            in_array($day % 100, [11, 12, 13]) => 'th',
            $day % 10 === 1 => 'st',
            $day % 10 === 2 => 'nd',
            $day % 10 === 3 => 'rd',
            default => 'th'
        };

        return "on the {$day}<sup>{$suffix}</sup> of {$date->format('F Y')}";
    }

    private function arabicDate(Carbon $date): string
    {
        return $date->locale('ar')->translatedFormat('j F Y');
    }
}
