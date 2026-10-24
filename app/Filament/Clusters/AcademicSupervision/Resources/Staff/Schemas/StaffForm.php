<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Staff\Schemas;

use App\Models\Reg\LtColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label(__('reg.name_ar'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('degree')
                    ->label(__('Attendance.degree'))
                    ->datalist([
                        'استاذ',
                        'استاذ مساعد',
                        'استاذ مشارك',
                        'محاضر',
                        'مساعد تدريس',
                        'تقني',
                    ])
                    ->required()
                    ->maxLength(255),

                Select::make('user_id')
                    ->label(__('Attendance.staff'))
                    ->relationship('user', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->live() // Add live() to update the subject options when staff changes
                    ->columnSpan(['md' => 1]),

                DatePicker::make('date_degree')
                    ->label(__('Attendance.date_degree'))
                    ->required(),

                TextInput::make('specialization')
                    ->label(__('Attendance.specialization'))
                    ->required()
                    ->maxLength(255),

                Radio::make('appointment')
                    ->label(__('Attendance.appointment'))
                    ->required()
                    ->inline()
                    ->options([
                        'basic' => 'Basic',
                        'temp' => 'Temporary',
                    ]),

                Radio::make('gender_code')->inline()
                    ->options(LtColumn::all()
                        ->where('grouped', '1001')
                        ->pluck('name_ar', 'code'))
                    ->required()
                    ->label(__('reg.gender')),

                TextInput::make('crch_load')
                    ->label(__('Crch_load'))
                    ->required()
                    ->maxLength(2),
            ])->columns(4);

    }
}
