<?php

namespace App\Reg;

enum PlanStatus: string
{
    // 'Bachelor','Postgraduate','diploma'
    case Main = 'Main';
    case Bachelor = 'Bachelor';
    case Postgraduate = 'Postgraduate';
    case diploma = 'diploma';

    public function getLabel(): ?string
    {
        // return $this->name;

        // or

        return match ($this) {
            self::Main => 'Main',
            self::Bachelor => 'Bachelor',
            self::Postgraduate => 'Postgraduate',
            self::diploma => 'diploma',
        };
    }
}

// Using the enum label
