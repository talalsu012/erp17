<?php

namespace App\Models\attendances;

use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupingStudent extends Pivot
{
    protected $table = 'att_grouping_student';

    public $incrementing = false;

    protected $fillable = [
        'grouping_id',
        'university_no',
        'batch_code',
        'room_id',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'university_no', 'university_no');
    }

    public function grouping(): BelongsTo
    {
        return $this->belongsTo(Grouping::class, 'grouping_id');
    }
}
