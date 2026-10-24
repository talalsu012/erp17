<?php

namespace App\Filament\Clusters\Reg\Resources\Medicals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MedicalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university_no')
                    ->label(__('reg.university_no'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.name_ar')
                    ->label(__('reg.student'))
                    ->sortable(),

                TextColumn::make('student.faculty.name_ar')
                    ->label(__('reg.faculty'))
                    ->sortable(),

                TextColumn::make('student.batch.name_ar')
                    ->label(__('reg.batch'))
                    ->sortable(),

                BadgeColumn::make('medical_fitness')
                    ->label(__('reg.medical_status'))
                    ->colors([
                        'success' => 'fit',
                        'danger' => 'unfit',
                    ])
                    ->formatStateUsing(fn ($state) => __(
                        $state === 'fit' ? 'reg.fit' : 'reg.unfit'
                    )),

                BadgeColumn::make('college_interview')
                    ->label(__('reg.college_interview'))
                    ->colors([
                        'success' => 'passed',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn ($state) => __(
                        $state === 'passed' ? 'reg.passed' : 'reg.failed'
                    )),

                BadgeColumn::make('final_decision')
                    ->label(__('reg.final_decision'))
                    ->colors([
                        'success' => 'accepted',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'accepted' => __('reg.accepted'),
                        'pending' => __('reg.pending'),
                        'rejected' => __('reg.rejected'),
                        default => $state,
                    }),

                TextColumn::make('decision_date')
                    ->label(__('reg.decision_date'))
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('decidedBy.name')
                    ->label(__('reg.decided_by'))
                    ->default('-'),

                TextColumn::make('medical_notes')
                    ->label(__('reg.medical_notes'))
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->medical_notes),

                TextColumn::make('interview_notes')
                    ->label(__('reg.interview_notes'))
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->interview_notes),
            ])
            ->filters([

                SelectFilter::make('medical_fitness')
                    ->label(__('reg.medical_status'))
                    ->options([
                        'fit' => __('reg.fit'),
                        'unfit' => __('reg.unfit'),
                    ]),

                SelectFilter::make('college_interview')
                    ->label(__('reg.college_interview'))
                    ->options([
                        'passed' => __('reg.passed'),
                        'failed' => __('reg.failed'),
                    ]),

                SelectFilter::make('final_decision')
                    ->label(__('reg.final_decision'))
                    ->options([
                        'accepted' => __('reg.accepted'),
                        'pending' => __('reg.pending'),
                        'rejected' => __('reg.rejected'),
                    ]),

                SelectFilter::make('student.faculty_code')
                    ->label(__('reg.faculty'))
                    ->options(
                        \App\Models\Reg\Faculty::pluck('name_ar', 'code')
                    ),

                SelectFilter::make('student.batch_code')
                    ->label(__('reg.batch'))
                    ->options(
                        \App\Models\Reg\LtColumn::where('grouped', 2003)->pluck('name_ar', 'code')
                    ),

                Filter::make('decision_date')
                    ->form([
                        DatePicker::make('from')->label(__('From date')),
                        DatePicker::make('to')->label(__('To date')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('decision_date', '>=', $date))
                        ->when($data['to'], fn ($q, $date) => $q->whereDate('decision_date', '<=', $date))
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('6xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
