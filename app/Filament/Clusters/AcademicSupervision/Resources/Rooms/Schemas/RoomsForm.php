<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoomsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Attendance.room_name'))
                    ->required()
                    ->maxLength(255),
                Select::make('campus_code')
                    ->relationship('campus', 'name_ar')
                    ->preload()
                    ->searchable()->label(__('reg.campus')),

                TextInput::make('capacity')
                    ->label(__('Attendance.room_capacity'))
                    ->required()
                    ->numeric()
                    ->minValue(1),

                Select::make('type')
                    ->label(__('Attendance.room_type'))
                    ->options([
                        'theoretical' => 'Theoretical',
                        'practical' => 'Practical',
                    ])
                    ->required(),
            ]);
    }
}
