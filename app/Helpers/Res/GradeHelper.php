<?php

namespace App\Helpers\Res;

use App\Models\Res\Grade;

class GradeHelper
{
    protected static $gradesCache = [];

    /**
     * Initialize the grades cache for a specific plan
     */
    protected static function initializeGradesCache(string $plan): void
    {
        self::$gradesCache[$plan] ??= Grade::where('plan', $plan)
            ->orderBy('upper', 'desc')
            ->get();
    }

    /**
     * Get grade title based on mark and plan
     */
    public static function getGradeTitle(float $mark, string $plan = '20060001'): ?Grade // 20060001 is the default plan code for bachelor
    {
        self::initializeGradesCache($plan);

        return self::$gradesCache[$plan]->first(fn ($grade) => $mark >= $grade->lower && $mark <= $grade->upper);
    }

    /**
     * Get grade point based on mark and plan
     */
    public static function getGradePoint(float $mark, string $plan = '20060001'): float // 20060001 is the default bachelor
    {
        $grade = self::getGradeTitle($mark, $plan);

        return $grade ? $grade->point : 0.0;
    }

    /**
     * Get all grades for a specific plan
     */
    public static function getGradesForPlan(string $plan): array
    {
        self::initializeGradesCache($plan);

        return self::$gradesCache[$plan]->all();
    }

    /**
     * Check if a mark is passing for a specific plan
     */
    public static function isPassingGrade(float $mark, string $plan = 'Bachelor'): bool
    {
        $grade = self::getGradeTitle($mark, $plan);

        return $grade ? $grade->point > 0 : false;
    }

    /**
     * Clear the cache for a specific plan or all plans
     */
    public static function clearCache(?string $plan = null): void
    {
        if ($plan) {
            unset(self::$gradesCache[$plan]);
        } else {
            self::$gradesCache = [];
        }
    }
}
