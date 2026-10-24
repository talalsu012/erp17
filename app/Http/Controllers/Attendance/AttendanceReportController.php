<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\attendances\Lecture;

class AttendanceReportController extends Controller
{
    public function ShowStudentAttendanceReport($lectureID)
    {

        $lecture = Lecture::with(['grouping.students.batch', 'grouping.students.level'])
            ->where('id', $lectureID)
            ->first();
        // Check if lecture exists
        if (! $lecture) {
            abort(404, 'المحاضرة غير موجودة');
        }

        $students = $lecture->grouping->students()
            ->with(['batch', 'level'])
            ->where('room_id', $lecture->room_id)
            ->orderBy('name_ar')
            ->get();

        $batchName = optional($students->first())->batch->name_ar ?? 'غير محدد';
        $levelName = optional($students->first())->level->name_ar ?? 'غير محدد';

        $group = $lecture->grouping;

        return view('Attendance.ShowStudentAttendanceReport', [
            'group' => $group,
            'lecture' => $lecture,
            'batchName' => $batchName,
            'levelName' => $levelName,
            'students' => $students,
        ]);
    }
}
