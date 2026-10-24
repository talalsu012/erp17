<?php

namespace App\Models\attendances;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LectureStaff extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'att_lecture_staff';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lecture_id',
        'is_excutive',
        'date_excutive',
        'actual_excutive_start_time',
        'actual_excutive_end_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_excutive' => 'boolean',
        'date_excutive' => 'date',
        'actual_excutive_start_time' => 'datetime:H:i',
        'actual_excutive_end_time' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the lecture associated with this record.
     */
    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'lecture_id');
    }

    /**
     * Scope for executive lectures.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExecutive($query)
    {
        return $query->where('is_excutive', true);
    }

    /**
     * Update executive time records.
     *
     * @param  int  $lectureId
     * @param  string  $date
     * @param  string  $startTime
     * @param  string  $endTime
     * @return bool
     */
    public static function updateExecutiveTime($lectureId, $date, $startTime, $endTime)
    {
        return self::updateOrCreate(
            ['lecture_id' => $lectureId],
            [
                'is_excutive' => true,
                'date_excutive' => $date,
                'actual_excutive_start_time' => $startTime,
                'actual_excutive_end_time' => $endTime,
            ]
        );
    }
}
