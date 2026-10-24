<?php

namespace App\Livewire\Res;

use App\Models\Res\Mark;
use App\Models\Student;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;
use Livewire\Component;

class PrintBoard extends Component
{
    public $PrintHeaderTable = [];

    public $PrintDataTable = [];

    public $CoursePrint = [];

    #[On('ShowSubjectHeaderFn')]
    public function setPrintHeader($parHeaderTablePrint = [], $parCourseTablePrint = 0)
    {
        $this->PrintHeaderTable = $parHeaderTablePrint;
        $this->PrintDataTable = $this->setColumnSelection($parHeaderTablePrint);

        // $courseName = Course::where('courses.code','=', $parCourseTablePrint)
        //         ->join('course_subject','courses.code','course_subject.course_code')
        //         ->join('subjects','course_subject.subject_code','subjects.code')
        //         ->select('subjects.name_ar','subjects.crch','subjects.code')
        //         ->get();
        // $this->CoursePrint = $courseName;
    }

    public function setColumnSelection($col)
    {
        if (! in_array('university_no', $col)) {
            $col = Arr::prepend($col, 'university_no');
        }

        $data = Student::join('batches', 'students.batch_code', 'batches.code')
            ->join('courses', 'batches.course_code', 'courses.code')
            ->select($col)
            ->get();

        return $data;

    }

    public function getMarkData($university_no = 9357405, $subject = 2126)
    {
        $data = Mark::where('university_no', '=', $university_no)
            ->where('subject_code', '=', $subject)
            ->select('grade', 'mark', 'point')
            ->get();

        return $data;

    }

    public function render()
    {
        return view('livewire.res.print-board');
    }
}
