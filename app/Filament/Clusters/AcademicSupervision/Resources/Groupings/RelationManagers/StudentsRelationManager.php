<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Groupings\RelationManagers;

use App\Models\attendances\Room;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Student;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'Students';

    // protected static ?string $recordTitleAttribute = 'name_ar';

    public int $countStd = 0;

    public int $limited;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_ar')
            ->query(function (Builder $query) {
                $query
                    ->with(['groupings.pivot.room'])
                    ->with([
                        'groupings' => function (Relation $relation) {
                            $relation->withPivot(['batch_code', 'room_id']);
                            $relation->with('pivot.room');
                        },
                    ]);
            })
            ->columns([
                TextColumn::make('university_no')
                    ->label(__('reg.university_no')),
                TextColumn::make('name_ar')
                    ->label(__('reg.name_ar')),
                TextColumn::make('faculty.name_ar')
                    ->label(__('reg.faculty')),
                TextColumn::make('batch.name_ar')
                    ->label(__('reg.batch')),
                TextColumn::make('level.name_ar')
                    ->label(__('reg.level')),

                TextColumn::make('room_name')
                    ->label(__('Attendance.room'))
                    ->getStateUsing(function ($record) {
                        $grouping = $this->getOwnerRecord();
                        $pivot = $record->groupings()
                            ->where('att_grouping_student.grouping_id', $grouping->id)
                            ->first()?->pivot;

                        return $pivot?->room?->name ?? 'N/A';
                    })
                    ->listWithLineBreaks(),

            ])
            ->filters([
                SelectFilter::make('faculty')
                    ->relationship('faculty', 'name_ar')->label(__('reg.faculty'))
                    ->searchable(),
                SelectFilter::make('batch')->label(__('reg.batch'))
                    ->relationship('batch', 'name_ar')
                    ->searchable(),
                SelectFilter::make('level')->label(__('reg.level'))
                    ->relationship('level', 'name_ar')
                    ->searchable(),
                SelectFilter::make('room_id')
                    ->label(__('Attendance.room'))
                    ->options(Room::query()->pluck('name', 'id'))
                    ->searchable()
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('groupings', function ($q) use ($data) {
                                $q->where('att_grouping_student.grouping_id', $this->getOwnerRecord()->id)
                                    ->where('att_grouping_student.room_id', $data['value']);
                            });
                        }
                    }),

            ])
            ->headerActions([
                AttachAction::make()
                    ->modalWidth('6xl')
                    ->form([
                        Grid::make(3)
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Radio::make('gender_code')->inline()
                                            ->options(LtColumn::all()->where('grouped', '1001')->pluck('name_ar', 'code'))
                                            ->live()
                                            ->label(__('reg.gender')),

                                        Select::make('faculty_code')
                                            ->label(__('reg.faculty'))
                                            ->options(Faculty::query()->pluck('name_ar', 'code'))
                                            ->live()
                                            ->afterStateUpdated(fn (Set $set) => $set('batch_code', null)), // تصحيح Set

                                        Select::make('room_id')
                                            ->label(__('Attendance.room'))
                                            ->options(fn () => Room::query()->pluck('name', 'id'))
                                            ->live()
                                            ->required(),

                                        Select::make('batch_code')
                                            ->label(__('reg.batch'))
                                            ->options(
                                                // تصحيح Get
                                                fn (Get $get) => LtColumn::query()
                                                    ->where('grouped', '2003')
                                                    ->pluck('name_ar', 'code'))
                                            ->live()
                                            ->required(),

                                        Select::make('level_code')
                                            ->label(__('reg.level'))
                                            ->options(fn () => Level::query()->pluck('name_ar', 'code'))
                                            ->live(),

                                        CheckboxList::make('students_by_letter')
                                            ->label(__('Filter by Alphabetical Group'))
                                            ->options(function (Get $get) { // تصحيح Get
                                                $query = Student::query();

                                                if ($facultyCode = $get('faculty_code')) {
                                                    $query->where('students.faculty_code', $facultyCode);
                                                }
                                                if ($genderCode = $get('gender_code')) {
                                                    $query->where('students.gender_code', $genderCode);
                                                }
                                                if ($batchCode = $get('batch_code')) {
                                                    $query->where('students.batch_code', $batchCode);
                                                }
                                                if ($levelCode = $get('level_code')) {
                                                    $query->where('students.level_code', $levelCode);
                                                }

                                                // 🚀 تحسين الأداء: جلب الأسماء فقط بدلاً من الكائنات بالكامل لمنع استهلاك الذاكرة
                                                $studentsNames = $query->pluck('name_ar');

                                                $groupedStudents = $studentsNames->groupBy(function ($name) {
                                                    if (empty($name)) {
                                                        return null;
                                                    }

                                                    return mb_substr($name, 0, 1, 'UTF-8');
                                                })
                                                    ->filter(fn ($group, $letter) => $letter !== null)
                                                    ->mapWithKeys(fn ($group, $letter) => [$letter => "{$letter} ({$group->count()})"])
                                                    ->sortKeys();

                                                return $groupedStudents->toArray();
                                            })
                                            ->live()
                                            ->bulkToggleable()
                                            ->columns(4)
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpan(1),

                                Group::make()
                                    ->schema([
                                        Placeholder::make('student_count')
                                            ->label(__('reg.students'))
                                            ->content(function (Get $get) { // تصحيح Get
                                                $query = Student::query();
                                                $hasActiveFilters = false;

                                                if ($facultyCode = $get('faculty_code')) {
                                                    $query->where('students.faculty_code', $facultyCode);
                                                    $hasActiveFilters = true;
                                                }
                                                if ($genderCode = $get('gender_code')) {
                                                    $query->where('students.gender_code', $genderCode);
                                                    $hasActiveFilters = true;
                                                }
                                                if ($batchCode = $get('batch_code')) {
                                                    $query->where('students.batch_code', $batchCode);
                                                    $hasActiveFilters = true;
                                                }
                                                if ($levelCode = $get('level_code')) {
                                                    $query->where('students.level_code', $levelCode);
                                                    $hasActiveFilters = true;
                                                }

                                                if ($studentsByLetter = $get('students_by_letter')) {
                                                    $query->where(function ($q) use ($studentsByLetter) {
                                                        foreach ($studentsByLetter as $letter) {
                                                            $q->orWhere('students.name_ar', 'like', "$letter%");
                                                        }
                                                    });
                                                    $hasActiveFilters = true;
                                                }

                                                if (! $hasActiveFilters) {
                                                    $query->limit(30);
                                                }

                                                return $query->count().'  '.__('reg.student');
                                            }),
                                        // 🛑 الدالة live() تم إزالتها من هنا لأنها كانت تسبب الخطأ 500

                                        CheckboxList::make('university_no')
                                            ->label(__('reg.students'))
                                            ->required()
                                            ->options(function (Get $get) {
                                                // 👇 أضف شرط استبعاد الأسماء الفارغة هنا
                                                $query = Student::query()->whereNotNull('name_ar');

                                                $hasActiveFilters = false;

                                                if ($facultyCode = $get('faculty_code')) {
                                                    $query->where('students.faculty_code', $facultyCode);
                                                    $hasActiveFilters = true;
                                                }
                                                if ($genderCode = $get('gender_code')) {
                                                    $query->where('students.gender_code', $genderCode);
                                                    $hasActiveFilters = true;
                                                }
                                                if ($batchCode = $get('batch_code')) {
                                                    $query->where('students.batch_code', $batchCode);
                                                    $hasActiveFilters = true;
                                                }
                                                if ($levelCode = $get('level_code')) {
                                                    $query->where('students.level_code', $levelCode);
                                                    $hasActiveFilters = true;
                                                }

                                                if ($studentsByLetter = $get('students_by_letter')) {
                                                    $query->where(function ($q) use ($studentsByLetter) {
                                                        foreach ($studentsByLetter as $letter) {
                                                            $q->orWhere('students.name_ar', 'like', "$letter%");
                                                        }
                                                    });
                                                    $hasActiveFilters = true;
                                                }

                                                if (! $hasActiveFilters) {
                                                    $query->limit(30);
                                                }

                                                return $query->orderBy('students.name_ar', 'asc')
                                                    ->pluck('name_ar', 'university_no')
                                                    ->toArray(); // 👈 إضافة toArray() لضمان إرجاع مصفوفة نقية لـ Filament
                                            })
                                            ->live()
                                            ->columns(3)
                                            ->gridDirection('row')
                                            ->searchable()
                                            ->bulkToggleable(),
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->mutateFormDataUsing(fn (array $data): array => [
                        'university_no' => $data['university_no'],
                        'batch_code' => $data['batch_code'], // Add this line
                        'room_id' => $data['room_id'], // Add this line
                    ])
                    ->action(function (array $data, AttachAction $action) {
                        $grouping = $this->getOwnerRecord();
                        $batchCode = $data['batch_code'];
                        $roomId = $data['room_id'];

                        foreach ((array) $data['university_no'] as $universityNo) {
                            $grouping->students()->attach($universityNo, [
                                'batch_code' => $batchCode,
                                'room_id' => $roomId, // Attach the room_id
                            ]);
                        }

                        $action->success();
                    })
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                // Tables\Actions\EditAction::make(),
                DetachAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
