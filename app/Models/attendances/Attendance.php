<?php

namespace App\Models\attendances;

use App\Models\Res\Subject;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $table = 'att_attendances';

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function group()
    {
        return $this->belongsTo(Grouping::class, 'group_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function attendanceStaff()
    {
        return $this->hasMany(AttendanceStaff::class, 'attendance_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'att_attendance_student', 'attendance_id', 'student_id');
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
