<?php

namespace App\Services\Res;

use App\Reg\ShowAbsentCode;
use Illuminate\Database\Eloquent\Builder;

class MarkEntryFilters
{
    public function allRequiredFiltersPresent(array $data): bool
    {
        return ! collect(['batch', 'subject', 'level', 'faculty', 'absent', 'semester'])
            ->contains(fn ($key) => empty($data[$key] ?? null));
    }

    public function applyAbsentFilter(Builder $query, $absentValue): void
    {
        if ($absentValue == 2) {
            $query->whereHas('recom', function ($q) {
                $q->whereIn('recom_code', ShowAbsentCode::getRemedialValues());
            });
        }
    }
}
