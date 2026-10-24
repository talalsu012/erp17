<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Schemas;

use App\Models\attendances\Grouping;
use App\Models\attendances\GroupingStudent;
use App\Models\attendances\Staff;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LectureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Group 1: Basic Information
                Section::make(__(''))
                    ->description(__('Enter the core attendance details'))
                    ->icon('heroicon-o-information-circle')
                    ->columnSpan('full')
                    ->schema([
                        Group::make()
                            ->schema([

                                Select::make('grouping_id')
                                    ->label(__('Attendance.grouping'))
                                    ->options(
                                        Grouping::whereNotNull('parent_id')
                                            ->get()
                                            ->pluck('name', 'id')
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(['md' => 1]),
                                Select::make('room_id')
                                    ->label(__('Attendance.room'))
                                    ->options(function (Get $get) {
                                        if (empty($get('grouping_id'))) {
                                            return [];
                                        }

                                        return GroupingStudent::query()
                                            ->where('grouping_id', $get('grouping_id'))
                                            ->get()
                                            ->pluck('room.name', 'room_id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(['md' => 1]),

                                Select::make('staff_id')
                                    ->label(__('Attendance.staff'))
                                    ->relationship('staff', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->live() // Add live() to update the subject options when staff changes
                                    ->columnSpan(['md' => 1]),

                                Select::make('subject_code')
                                    ->label(__('res.subject'))
                                    ->options(function (Get $get) {
                                        $staffId = $get('staff_id');
                                        if (! $staffId) {
                                            return [];
                                        }
                                        $staff = Staff::with(['subjects', 'subjects.faculty'])->find($staffId);

                                        if (! $staff) {
                                            return [];
                                        }

                                        return $staff->subjects->mapWithKeys(function ($subject) {
                                            $facultyName = $subject->faculty->name_ar ?? __('reg.faculty');
                                            $label = "{$subject->name_ar} - ({$facultyName})";

                                            return [$subject->code => $label];
                                        });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(['md' => 1]),

                            ])
                            ->columns(4),

                        Group::make()
                            ->schema([])
                            ->columns(2),

                        Group::make()
                            ->schema([
                                Select::make('day_of_week')
                                    ->label(__('Attendance.day_of_week'))
                                    ->options([
                                        'Monday' => __('Attendance.days.Monday'),
                                        'Tuesday' => __('Attendance.days.Tuesday'),
                                        'Wednesday' => __('Attendance.days.Wednesday'),
                                        'Thursday' => __('Attendance.days.Thursday'),
                                        'Friday' => __('Attendance.days.Friday'),
                                        'Saturday' => __('Attendance.days.Saturday'),
                                        'Sunday' => __('Attendance.days.Sunday'),
                                    ])
                                    ->required()
                                    ->columnSpan(['md' => 2]),

                                TimePicker::make('start_time')
                                    ->label(__('Attendance.start_time'))
                                    ->required()
                                    ->seconds(false)
                                    ->minutesStep(15)
                                    ->columnSpan(['md' => 1]),

                                TimePicker::make('end_time')
                                    ->label(__('Attendance.end_time'))
                                    ->required()
                                    ->seconds(false)
                                    ->minutesStep(15)
                                    ->columnSpan(['md' => 1]),

                                DatePicker::make('start_date')
                                    ->label(__('Attendance.start_date'))
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->closeOnDateSelection()
                                    ->columnSpan(['md' => 1]),

                                DatePicker::make('end_date')
                                    ->label(__('Attendance.end_date'))
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->closeOnDateSelection()
                                    ->columnSpan(['md' => 1]),
                            ])
                            ->columns(6),
                    ]),
            ]);
    }
}
