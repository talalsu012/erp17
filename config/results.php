<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Certificates read the approved academic record only
    |--------------------------------------------------------------------------
    |
    | When true, transcripts and certificates are built exclusively from the
    | approved record (res_record_marks). When false (transition), they use the
    | approved record when the student has one and fall back to the live marks
    | otherwise, so certificates of cohorts that were never approved keep working.
    |
    */

    'certificates_from_record_only' => (bool) env('RESULTS_CERTIFICATES_FROM_RECORD_ONLY', false),

    /*
    |--------------------------------------------------------------------------
    | Academic regulation thresholds (2020)
    |--------------------------------------------------------------------------
    |
    | Fractions of the semester credit hours as [numerator, denominator].
    | repeat_above: failing MORE than this fraction repeats the semester.
    | dismiss_above: failing MORE than this fraction dismisses (not in the final year).
    | final_year_levels: how many last levels of a department plan count as final year.
    |
    */

    'regulation' => [
        'repeat_above' => [1, 3],
        'dismiss_above' => [2, 3],
        'final_year_levels' => 2,
    ],

];
