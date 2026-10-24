<?php

namespace App\Models\attendances;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LectureStudent extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     *             SELECT lecture_id, universit_no, `status`	FROM att_lecture_student
     */
    protected $table = 'att_lecture_student';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    // public $incrementing = false;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    // protected $primaryKey = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lecture_id',
        'university_no',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the possible status values.
     */
    public static function getStatuses(): array
    {
        return ['present', 'absent', 'late'];
    }
}
