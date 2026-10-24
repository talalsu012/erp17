<?php

namespace App\Models\attendances;

use App\Models\Reg\Campus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $table = 'att_rooms';

    protected $fillable = [
        'name', 'campus_code', 'capacity', 'type',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'room_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_code', 'code');
    }

    // public function groupings()
    // {
    //     return $this->hasMany(Grouping::class, 'room_id');
    // }
}
