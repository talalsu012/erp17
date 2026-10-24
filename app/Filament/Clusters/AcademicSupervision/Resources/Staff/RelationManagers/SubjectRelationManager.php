<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Staff\RelationManagers;

use App\Filament\Clusters\Res\Resources\Subjects\SubjectsResource;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Res\Subject;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';

    // protected static ?string $relatedResource = SubjectsResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_ar')
            ->columns([
                TextColumn::make('name_ar')
                    ->label(__('res.subject'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('batch_code')
                    ->label(__('reg.batch'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('faculty.name_ar')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->label(__('reg.faculty')),
                TextColumn::make('department.name_ar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->label(__('reg.department')),
                TextColumn::make('level.name_ar')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->label(__('reg.level')),
                TextColumn::make('semester_id')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->label(__('res.semester')),
                TextColumn::make('name_en')
                    ->label(__('reg.name_en'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                AttachAction::make()
                    ->form(fn (AttachAction $action): array => [
                        Select::make('batch_code')
                            ->label(__('reg.batch'))
                            ->required()
                            ->options(LtColumn::query()->pluck('name_ar', 'code')),
                        Select::make('faculty_code')
                            ->label(__('reg.faculty'))
                            ->options(Faculty::get()->pluck('name_ar', 'code'))
                            ->live(),
                        Select::make('level_code')
                            ->label(__('reg.level'))
                            ->options(Level::all()->pluck('name_ar', 'code'))
                            ->live(),
                        Select::make('semester_id')->required()
                            ->label(__('res.semester'))
                            ->native()
                            ->options([
                                '1' => __('res.first_semester'),
                                '2' => __('res.second_semester'),

                            ])
                            ->selectablePlaceholder(false),
                        // $action->getRecordSelect(),
                        $action->getRecordSelect()
                            ->label(__('res.subject'))
                            ->multiple()
                            ->options(function (Get $get) {
                                // Initialize the query for subjects
                                $query = Subject::query();

                                // Filter by faculty if selected
                                if ($facultyCode = $get('faculty_code')) {
                                    $query->where('faculty_code', $facultyCode);
                                }

                                // Filter by level if selected - fixed table reference
                                if ($levelCode = $get('level_code')) {
                                    $query->where('level_code', $levelCode); // Changed from students.level_code to just level_code
                                }

                                if ($semester = $get('semester_id')) {
                                    $query->where('semester_id', $semester); // Changed from students.level_code to just level_code
                                }

                                return $query->pluck('name_ar', 'code');
                            })
                            //   ->searchable(['name_ar', 'code']) // Specify searchable columns
                            ->required()
                            ->preload() // Preload options for better performance
                            ->native(false), // Added native false for better UI
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
                // Tables\Actions\EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
