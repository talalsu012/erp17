<?php

namespace App\Http\Controllers;

use App\Models\Reg\Batch;
use App\Models\Result\Subject;

class BatchReportController extends Controller
{
    public function index()
    {
        $batches = Batch::with(['course', 'subjects'])
            ->where('is_visible', true)
            ->orderBy('batch_start', 'desc')
            ->get();

        return view('batch-reports.index', compact('batches'));
    }

    public function show($batchCode)
    {
        $batch = Batch::with([
            'course' => function ($query) {
                $query->with(['subjects' => function ($q) {
                    $q->with('marks');
                }]);
            },
            'subjects' => function ($query) {
                $query->with('marks');
            },
            'students',
        ])->findOrFail($batchCode);

        return view('batch-reports.show', compact('batch'));
    }

    public function courseSubjects($batchCode, $courseCode)
    {
        $batch = Batch::with([
            'course' => function ($query) use ($courseCode) {
                $query->where('code', $courseCode)
                    ->with(['subjects' => function ($q) {
                        $q->with(['marks' => function ($m) {
                            $m->orderBy('university_no');
                        }]);
                    }]);
            },
        ])->findOrFail($batchCode);

        return view('batch-reports.course-subjects', compact('batch'));
    }

    public function subjectMarks($batchCode, $subjectCode)
    {
        $batch = Batch::findOrFail($batchCode);
        $subject = Subject::with(['marks' => function ($query) use ($batchCode) {
            $query->where('batch_code', $batchCode)
                ->orderBy('university_no')
                ->with('student');
        }])->findOrFail($subjectCode);

        return view('batch-reports.subject-marks', compact('batch', 'subject'));
    }
}
