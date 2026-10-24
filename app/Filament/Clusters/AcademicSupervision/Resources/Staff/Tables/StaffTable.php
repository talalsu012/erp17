<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Staff\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('reg.name_ar'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('degree')
                    ->label(__('Attendance.degree'))
                    ->searchable(),

                TextColumn::make('specialization')
                    ->label(__('Attendance.specialization'))
                    ->searchable(),

                TextColumn::make('appointment')
                    ->label(__('Attendance.appointment'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'basic' => 'success',
                        'temp' => 'warning',
                    }),

                TextColumn::make('gender_code')
                    ->label(__('reg.gender'))
                    ->getStateUsing(fn ($record) => $record->gender?->name_ar ?? $record->gender_code),
            ])
            ->filters([
                SelectFilter::make('appointment')
                    ->label(__('Attendance.appointment'))
                    ->options([
                        'basic' => 'Basic',
                        'temp' => 'Temporary',
                    ]),

                SelectFilter::make('gender')
                    ->label(__('reg.gender'))
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
