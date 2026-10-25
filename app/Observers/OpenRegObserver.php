<?php

namespace App\Observers;

use App\Models\Reg\OpenReg;
use App\Services\Reg\StudentFeeService;

/**
 * فتح التسجيل أو إغلاقه أو تعديل فترته يغيّر المدفوع المعتدّ به، فتُعاد رسوم الطلاب الذين تشملهم الفترة.
 */
class OpenRegObserver
{
    public function __construct(private readonly StudentFeeService $fees) {}

    public function saved(OpenReg $window): void
    {
        $this->refresh($window);

        if ($window->wasChanged(['faculty_code', 'department_code', 'level_code', 'batch_code'])) {
            $this->refresh(new OpenReg([
                'faculty_code' => $window->getOriginal('faculty_code'),
                'department_code' => $window->getOriginal('department_code'),
                'level_code' => $window->getOriginal('level_code'),
                'batch_code' => $window->getOriginal('batch_code'),
            ]));
        }
    }

    public function deleted(OpenReg $window): void
    {
        $this->refresh($window);
    }

    public function restored(OpenReg $window): void
    {
        $this->refresh($window);
    }

    private function refresh(OpenReg $window): void
    {
        if (blank($window->faculty_code)) {
            return;
        }

        $this->fees->refreshFaculty($window->faculty_code, [
            'department' => $window->department_code,
            'level' => $window->level_code,
            'batch' => $window->batch_code,
        ]);
    }
}
