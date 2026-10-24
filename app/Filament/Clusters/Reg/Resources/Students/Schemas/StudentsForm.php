<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Schemas;

use App\Filament\Clusters\Reg\Resources\Students\Schemas\Tabs\BasicInfoTab;
use App\Filament\Clusters\Reg\Resources\Students\Schemas\Tabs\FeesTab;
use App\Filament\Clusters\Reg\Resources\Students\Schemas\Tabs\NotesTab;
use App\Filament\Clusters\Reg\Resources\Students\Schemas\Tabs\PersonalInfoTab;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class StudentsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Tabs::make(__('reg.basic_info'))
                ->tabs([

                    /* ============================================================
                     | BASIC INFO
                     ============================================================*/
                    BasicInfoTab::make(),
                    PersonalInfoTab::make(),
                    FeesTab::make(),
                    NotesTab::make(),
                ])
                ->columnSpanFull(),
        ]);
    }
}
