<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HeaderResult extends Component
{
    /**
     * Create a new component instance.
     */
    // sent Faculty, Department, Special, Level, Semester, Batch
    public function __construct(
        public $faculty,
        // public $department,
        // public $special,
        // public $level,
        // public $semester,
        // public $batch
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.header-result', [
            'faculty' => $this->faculty,
        ]);
    }
}
