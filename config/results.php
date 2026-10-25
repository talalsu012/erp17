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
    | Academic regulation thresholds
    |--------------------------------------------------------------------------
    |
    | Moved to config/regulations.php (decisions.repeat_above, decisions.dismiss_above,
    | decisions.final_year_levels) so every academic rule lives in one file.
    |
    */

];
