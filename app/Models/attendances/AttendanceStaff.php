<?php

namespace App\Models\attendances;

use Illuminate\Database\Eloquent\Model;

class AttendanceStaff extends Model
{
    protected $table = 'att_attendance_staff';

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
