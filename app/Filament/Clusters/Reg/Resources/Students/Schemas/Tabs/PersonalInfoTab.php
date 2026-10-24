<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Schemas\Tabs;

use App\Models\Reg\LtColumn;
use App\Reg\LtGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Str;

class PersonalInfoTab
{
    public static function make(): Tab
    {
        return Tab::make(__('reg.personal_info'))
            ->schema([

                Grid::make()
                    ->columns(4)
                    ->schema([

                        Section::make()
                            ->columnSpan(3)
                            ->schema([
                                Select::make('nationality_code')
                                    ->label(__('reg.nationality'))
                                    ->options(fn () => LtColumn::optionsFor(LtGroup::Nationality))
                                    ->searchable(),

                                Radio::make('gender_code')
                                    ->label(__('reg.gender'))
                                    ->inline()
                                    ->options(fn () => LtColumn::optionsFor(LtGroup::Gender))
                                    ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.gender_code'))
                                    ->dehydrated(),

                                DatePicker::make('birth_date')
                                    ->label(__('reg.birth_date'))
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.birth_date'))
                                    ->dehydrated(),
                            ]),

                        Section::make()
                            ->columnSpan(1)
                            ->schema([

                                FileUpload::make('photo')
                                    ->label(__('reg.profile_photo'))
                                    ->image()
                                    ->imageEditor()
                                    ->required()
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                    ])
                                    ->disk('public')
                                    ->directory('students/avatar')
                                    ->visibility('public')
                                    ->getUploadedFileNameForStorageUsing(function ($file, Get $get): string {
                                        $extension = $file->getClientOriginalExtension();

                                        $universityNo = $get('university_no');

                                        if (blank($universityNo)) {
                                            return 'std_temp_'.Str::random(5).'.'.$extension;
                                        }

                                        return 'std'.$universityNo.'.'.$extension;
                                    })
                                    ->live()
                                    ->columnSpan(1),
                            ]),
                    ]),

                Fieldset::make(__('reg.address'))
                    ->columns(4)
                    ->schema([
                        TextInput::make('email')->email()->unique(ignoreRecord: true),
                        TextInput::make('mobile')->tel(),
                        TextInput::make('city'),
                        TextInput::make('localty'),
                    ]),

                Fieldset::make(__('reg.guardian'))
                    ->columns(4)
                    ->schema([
                        TextInput::make('parent')->label(__('reg.guardian_name')),
                        Toggle::make('parent_alive')->label(__('reg.father_alive')),
                        TextInput::make('mother')->label(__('reg.mother_name')),
                        Toggle::make('mother_alive')->label(__('reg.mother_alive')),
                    ]),
            ]);
    }
}
