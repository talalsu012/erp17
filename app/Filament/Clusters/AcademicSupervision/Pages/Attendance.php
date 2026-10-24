<?php

namespace App\Filament\Clusters\AcademicSupervision\Pages;

use App\Filament\Clusters\AcademicSupervision\AcademicSupervisionCluster;
use App\Models\attendances\Lecture;
use App\Models\attendances\LectureStaff;
use App\Models\attendances\LectureStudent;
use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Attendance extends Page implements HasForms, HasTable
{
    use \Filament\Tables\Concerns\InteractsWithTable,
        InteractsWithForms;
    use HasPageShield;

    protected string $view = 'filament.clusters.academic-supervision.pages.attendance';

    protected static ?string $cluster = AcademicSupervisionCluster::class;

    // protected static ?string $title = 'Attendance.AttendanceReg';
    protected static ?int $navigationSort = 1;

    public function getTitle(): string|Htmlable
    {
        return __('Attendance.AttendanceReg');
    }

    public static function getLabel(): ?string
    {
        return __(static::$title);
    }

    public static function getPluralLabel(): ?string
    {
        return '';
    }

    public function getMaxContentWidth(): Width|string|null
    {
        //
        return Width::ScreenTwoExtraLarge;

    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('Attendance.AttendanceReg'));
    }

    public static function getNavigationGroup(): string
    {
        return __('Attendance.Attendance');
    }

    public $lecture;

    public $start_time_excutive;

    public $end_time_excutive;

    public $date_excutive;

    public $status;

    public $university_no;

    public $lectureID = \null; // Default to 1 or null as needed

    public function AttendanceForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columns([
                        'sm' => 5,
                    ])
                    ->schema([
                        Select::make('lecture')
                            ->label(__('Attendance.Lecture'))
                            ->options(fn () => Lecture::with(['staff', 'grouping.room'])
                                ->get()
                                ->mapWithKeys(function ($lecture) {
                                    $staffName = $lecture->staff?->name ?? 'Unknown Staff';
                                    $groupName = $lecture->grouping?->name ?? 'Unknown Group';
                                    $roomName = $lecture->grouping?->room?->name ?? 'Unknown Room';
                                    $lecturecode = $lecture->id;

                                    return [
                                        $lecture->id => sprintf(
                                            '%s | %s | %s | %s | %s | %s | %s',
                                            $lecture->day_of_week,
                                            $lecture->start_time->format('h:i A'),
                                            $lecture->end_time->format('h:i A'),
                                            $staffName,
                                            $groupName,
                                            $roomName,
                                            $lecturecode
                                        ),
                                    ];
                                }))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $this->lectureID = $state;
                                $this->lecture = $state; // Update the public property as well

                                // Set the time values when lecture is selected
                                if ($lecture = Lecture::find($state)) {
                                    $this->start_time_excutive = $lecture->start_time->format('H:i');
                                    $this->end_time_excutive = $lecture->end_time->format('H:i');
                                    $this->date_excutive = now()->format('Y-m-d');

                                    // Also update the form state if needed
                                    $set('start_time_excutive', $this->start_time_excutive);
                                    $set('end_time_excutive', $this->end_time_excutive);
                                    $set('date_excutive', $this->date_excutive);

                                }
                            })
                            ->searchable()
                            ->searchDebounce(500) // Add debounce for better performance
                            ->getSearchResultsUsing(fn (string $search) => Lecture::with(['staff', 'grouping.room'])
                                ->where('day_of_week', 'like', "%{$search}%")
                                ->orWhereHas('staff', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('grouping', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('grouping.room', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (Lecture $lecture) => [
                                    // show lecture id in options for debugging, can be removed later
                                    $lecture->id => sprintf(
                                        '%s | %s | %s | %s | %s | %s | %s',
                                        $lecture->day_of_week,
                                        $lecture->start_time->format('h:i A'),
                                        $lecture->end_time->format('h:i A'),
                                        $lecture->staff?->name ?? 'Unknown Staff',
                                        $lecture->grouping?->name ?? 'Unknown Group',
                                        $lecture->grouping?->room?->name ?? 'Unknown Room',
                                        $lecture->id // Show ID for debugging
                                    ),
                                ]),
                                // ->updateStateUsing(function ($state) {
                                //     if ($lecture = Lecture::find($state)) {
                                //         $this->lectureID = $lecture->id; // Set the lectureID when search results are fetched
                                //     }
                                //     return $state;
                                // }),
                            )
                            ->columnSpan(4),

                        Actions::make([
                            Action::make('View')
                                ->action('LectureStudentFn')
                                // ->requiresConfirmation()
                                ->icon('heroicon-m-plus')
                                ->color('success')
                                ->outlined(),
                        ])

                            ->visible()
                            ->verticalAlignment(VerticalAlignment::End),

                    ]),
                Section::make([])
                    ->columns([
                        'sm' => 5,
                    ])
                    ->schema([
                        DatePicker::make('date_excutive')
                            ->label(__('Attendance.date_excutive'))
                            ->live()
                            ->required(),
                        TimePicker::make('start_time_excutive')
                            ->label(__('Attendance.start_time'))
                            ->live()
                            ->required()
                            ->seconds(false)
                            ->minutesStep(15)
                            ->columnSpan(['md' => 1]),
                        TimePicker::make('end_time_excutive')
                            ->label(__('Attendance.end_time'))
                            ->required()
                            ->seconds(false)
                            ->minutesStep(15)
                            ->columnSpan(['md' => 1]),

                        Actions::make([
                            Action::make('Excutive')
                                ->label(__('Update Schedule'))
                                ->action('LectureExcutiveFn')
                                ->requiresConfirmation()
                                ->modalHeading(__('Update Lecture Schedule'))
                                ->modalDescription(__('Are you sure you want to update this lecture\'s date and time?'))
                                ->icon('heroicon-m-clock')
                                ->color('info')
                                ->outlined(),
                        ])
                            ->visible(fn () => ! empty($this->lectureID))
                            ->verticalAlignment(VerticalAlignment::End),

                    ])
                    ->grow(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns($this->getColumns())
            ->recordClasses(fn (Model $record) => match ($record->status) {
                'draft' => 'opacity-30',
                'reviewing' => 'border-s-2 border-orange-600 dark:border-orange-300',
                'published' => 'border-s-2 border-green-600 dark:border-green-300',
                default => null,
            })
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->paginationPageOptions([10, 25, 50, 'all'])
            ->filtersFormWidth(Width::FourExtraLarge)
            ->recordActions([])
            ->toolbarActions([]);
    }

    protected function baseQuery(): Builder
    {
        return Student::query()
            ->whereHas('groupings.lecture', function ($query) {
                $query->where('id', $this->lectureID);
            })
            ->with(['groupings' => function ($query) {
                $query->whereHas('lecture', function ($q) {
                    $q->where('id', $this->lectureID);
                });
            }])
            ->with(['lectures' => function ($query) {
                $query->where('lecture_id', $this->getAttendanceId());
            }])
            ->orderBy('name_ar');
    }

    protected function getColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('#')
                ->rowIndex()
                ->listWithLineBreaks(),

            SelectColumn::make('attendance_status')
                ->label(__('Attendance.AttendanceReg'))
                ->options([
                    '' => __('  '), // Placeholder option
                    'present' => __('Present'),
                    'absent' => __('Absent'),
                    'late' => __('Late'),
                ])
                ->default('') // Set default to placeholder
                ->state(function (Student $record) {
                    return $this->getAttendanceStatus($record) ?? ''; // Return empty string if null
                })
                ->updateStateUsing(function (Student $record, $state) {
                    if (empty($state)) {
                        return; // Don't save if placeholder is selected
                    }
                    $this->updateAttendance($record, $state);
                })
                ->selectablePlaceholder(true) // Allow placeholder selection
                ->disableOptionWhen(fn ($value) => ! in_array($value, ['present', 'absent', 'late', '']))
                ->width('150px')
                ->rules(['required', 'in:present,absent,late']), // Validation

            TextColumn::make('name_ar')
                ->label(__('reg.name_ar'))
                ->listWithLineBreaks(),

            TextColumn::make('university_no')
                ->label(__('reg.university_no'))
                ->listWithLineBreaks(),

        ];
    }

    protected function getFilters(): array
    {
        return [
            // SelectFilter::make('faculty')
            //     ->label(__('reg.faculty'))
            //     ->relationship('faculty', 'name')
            //     ->attribute('faculty_code')
            //     ->searchable()
            //     ->multiple()
            //     ->preload(),

        ];
    }

    // In your Livewire component
    public function LectureExcutiveFn(): void
    {
        $this->validate([
            'date_excutive' => 'required|date',
            'start_time_excutive' => 'required|date_format:H:i',
            'end_time_excutive' => 'required|date_format:H:i|after:start_time_excutive',
        ]);

        try {
            DB::transaction(function () {
                // Lecture::where('id', $this->lectureID)
                //     ->update([
                //         'date_excutive' => $this->date_excutive,
                //         'end_date' => $this->date_excutive,
                //         'start_time' => $this->start_time_excutive,
                //         'end_time' => $this->end_time_excutive,
                //         'updated_at' => now(),
                //     ]);

                // Update all related attendance records if needed
                LectureStaff::updateExecutiveTime(
                    $lectureId = $this->lectureID,
                    $date = $this->date_excutive,
                    $startTime = $this->start_time_excutive,
                    $endTime = $this->end_time_excutive
                );
            });

            Notification::make()
                ->title(__('Schedule Updated'))
                ->body(__('Lecture date and time have been successfully updated.'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Update Failed'))
                ->body(__('Error updating lecture schedule: ').$e->getMessage())
                ->danger()
                ->send();

            throw $e; // Re-throw for debugging
        }
    }

    public function LectureStudentFn()
    {
        $this->resetPage();
        $this->lectureID = $this->lecture;

    }

    protected function getAttendanceId(): int
    {
        return Lecture::firstOrCreate([
            'id' => $this->lectureID,
            // 'date' => now()->toDateString(),
        ])->id;
    }

    protected function formatLectureOption(Lecture $lecture): string
    {
        return sprintf(
            '%s | %s-%s | %s | %s | %s',
            $lecture->day_of_week,
            $lecture->start_time->format('h:i A'),
            $lecture->end_time->format('h:i A'),
            $lecture->staff?->name ?? 'Unknown Staff',
            $lecture->grouping?->name ?? 'Unknown Group',
            $lecture->grouping?->room?->name ?? 'Unknown Room'
        );
    }

    protected function getAttendanceStatus(Student $student): ?string
    {
        return $student->lectures->first()?->pivot->status;
    }

    protected function updateAttendance(Student $student, string $status): void
    {
        $attendanceId = $this->getAttendanceId();

        $student->lectures()->syncWithoutDetaching([
            $attendanceId => ['status' => $status],
        ]);

        // Or use this if you want to replace existing attendance
        // $student->attendances()->sync([$attendanceId => ['status' => $status]]);
    }

    public static function InsertAttendace($university_no, $lectureID, $status)
    {

        //  \dd($university_no, $lectureID, $status);

        try {
            LectureStudent::create([
                'university_no' => $university_no,
                'lecture_id' => $lectureID,
                'status' => $status,
            ]);

            Notification::make()
                ->title('Success!')
                ->body('Data saved successfully.')
                ->success() // Sets the notification type to "success"
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Failed to save data: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
