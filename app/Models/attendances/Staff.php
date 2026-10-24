<?php

namespace App\Models\attendances;

use App\Models\Reg\LtColumn;
use App\Models\Res\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    protected $table = 'att_staff';

    protected $fillable = [
        'name', 'degree', 'date_degree', 'crch_load',
        'specialization', 'appointment', 'gender_code',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'att_staff_subject')
            ->withPivot('batch_code');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'staff_id');
    }

    public function attendanceStaff()
    {
        return $this->hasMany(AttendanceStaff::class, 'staff_id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'gender_code')
            ->where('grouped', '1001');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
