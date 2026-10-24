<?php

namespace App\Models\attendances;

use App\Models\Res\Subject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecture extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'att_lectures';

    protected $fillable = [
        'subject_code',
        'staff_id',
        'room_id',
        'start_time',
        'end_time',
        'day_of_week',
        'start_date',
        'end_date',
        'grouping_id',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the subject associated with the lecture.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_code', 'code');
    }

    /**
     * Get the staff member associated with the lecture.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the room associated with the lecture.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function grouping(): BelongsTo
    {
        return $this->belongsTo(Grouping::class);
    }

    public function lectureStaff()
    {
        return $this->hasOne(LectureStaff::class, 'lecture_id');
    }
}
