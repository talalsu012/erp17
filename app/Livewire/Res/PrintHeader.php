<?php

namespace App\Livewire\Res;

use Livewire\Attributes\On;
use Livewire\Component;

class PrintHeader extends Component
{
    public $fac = [];

    #[On('ShowSubjectHeaderFn')]
    public function setHeaderValue($parHeaderTablePrint = [])
    {
        $this->fac = $parHeaderTablePrint;
    }

    public function render()
    {
        return view('livewire.res.print-header');
    }
}
