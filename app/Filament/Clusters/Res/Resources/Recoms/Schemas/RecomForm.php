<?php

namespace App\Filament\Clusters\Res\Resources\Recoms\Schemas;

use App\Models\Res\LtRecom;
use App\Models\Student;
use App\Reg\AbsStatus;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('reg.basic_info'))
                    ->schema([
                        Grid::make(6)
                            ->schema([
                                Select::make('university_no')
                                    ->label(__('reg.university_no'))
                                    ->relationship('student', 'name_ar')
                                    // ->options(function ($get) {
                                    //     return Student::query()
                                    //         ->selectRaw('university_no, CONCAT(name_ar, " (", university_no, ")") as name_with_code')
                                    //         ->pluck('name_with_code', 'university_no');
                                    // })
                                    ->live()
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(2),

                                Select::make('level_code')
                                    ->label(__('reg.level'))
                                    ->relationship('level', 'name_ar')
                                    ->required()
                                    ->preload()
                                    ->searchable(),

                                Select::make('batch_code')
                                    ->label(__('reg.batch'))
                                    ->relationship('batch', 'code')
                                    ->required()
                                    ->preload()
                                    ->searchable(),

                            ]),
                    ])->columnSpan(6),

                Section::make(__('res.ResLtRecoms'))
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                // Column 1: Status
                                Select::make('recom_code')
                                    ->label(__('res.ResLtRecoms'))
                                    ->options(LtRecom::all()->pluck('name_ar', 'code'))
                                    ->preload()
                                    ->required(),

                                Hidden::make('semester')->default(\App\Services\Res\RegulationProfile::current()->defaultSemester()),

                                Select::make('examType')
                                    ->label(__('res.absent'))
                                    ->options(AbsStatus::class)
                                    ->preload()
                                    ->required(),

                                TextInput::make('countSubject')->label(__('res.CountSubject'))->numeric(),

                                TextInput::make('sumMarks')->label(__('res.SumMarks'))->numeric(),
                                Textinput::make('crch')->label(__('res.CrCh'))->numeric(),
                                Textinput::make('points')->label(__('res.Points'))->numeric(),

                                TextInput::make('gpa')->label(__('res.gpa'))->numeric(),

                                TextInput::make('cgpa')
                                    ->label(__('res.CGPA'))
                                    ->numeric(),
                                TextInput::make('note')
                                    ->label(__('res.Note')),
                            ]),
                    ])->columnSpan(5),
            ]);
    }
}
