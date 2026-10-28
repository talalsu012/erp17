<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Schemas\Tabs;

use App\Filament\Clusters\Reg\Resources\Students\Schemas\StudentFeesSchema;
use Filament\Schemas\Components\Tabs\Tab;

class FeesTab
{
    public static function make(): Tab
    {
        return Tab::make(__('reg.fees'))
            ->schema(StudentFeesSchema::components());
    }
}
