<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromView;

class StudentExport implements FromView
{
    public function __construct(protected Builder $query) {}

    public function view(): View
    {
        return view('exports.student', [
            'students' => $this->query->get(),
        ]);
    }
}
