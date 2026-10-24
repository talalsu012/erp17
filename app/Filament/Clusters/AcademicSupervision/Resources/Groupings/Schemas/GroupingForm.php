<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Groupings\Schemas;

use App\Models\attendances\Grouping;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GroupingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Attendance.name'))
                    ->required()
                    ->maxLength(255),

                Select::make('parent_id')
                    ->label(__('Attendance.parent'))
                    ->options(Grouping::whereNull('parent_id')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),

                Textarea::make('note')
                    ->label(__('Attendance.note'))
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }
}
