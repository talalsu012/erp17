<?php

namespace App\Filament\Clusters\Reg\Resources\OpenRegs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OpenRegsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('faculty_code')
                    ->label(__('reg.faculty'))
                    ->getStateUsing(fn ($record) => $record->faculty?->name_ar ?? $record->faculty_code),
                TextColumn::make('department_code')
                    ->label(__('reg.department'))
                    ->getStateUsing(fn ($record) => $record->department?->name_ar ?? $record->department_code),
                TextColumn::make('level_code')
                    ->label(__('reg.level'))
                    ->getStateUsing(fn ($record) => $record->level?->name_ar ?? $record->level_code),
                TextColumn::make('batch_code')
                    ->label(__('reg.batch'))
                    ->getStateUsing(fn ($record) => $record->batch?->name_ar ?? $record->batch_code),
                TextColumn::make('start_date')
                    ->label(__('reg.start_date'))
                    ->date(),
                TextColumn::make('end_date')
                    ->label(__('reg.end_date'))
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('faculty_code')
                    ->label(__('reg.faculty'))
                    ->options(\App\Models\Reg\Faculty::pluck('name_ar', 'code')),
                SelectFilter::make('department_code')
                    ->label(__('reg.department'))
                    ->options(\App\Models\Reg\Department::pluck('name_ar', 'code')),
                SelectFilter::make('level_code')
                    ->label(__('reg.level'))
                    ->options(\App\Models\Reg\Level::pluck('name_ar', 'code')),
                SelectFilter::make('batch_code')
                    ->label(__('reg.batch'))
                    ->options(\App\Models\Reg\LtColumn::pluck('name_ar', 'code')),
                Filter::make('start_date')
                    ->form([
                        DatePicker::make('start_date_from')->label(__('reg.start_date_from')),
                        DatePicker::make('start_date_to')->label(__('reg.start_date_to')),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['start_date_from'], fn ($q) => $q->where('start_date', '>=', $data['start_date_from']))
                        ->when($data['start_date_to'], fn ($q) => $q->where('start_date', '<=', $data['start_date_to']))),
                Filter::make('end_date')
                    ->form([
                        DatePicker::make('end_date_from')->label(__('reg.end_date_from')),
                        DatePicker::make('end_date_to')->label(__('reg.end_date_to')),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['end_date_from'], fn ($q) => $q->where('end_date', '>=', $data['end_date_from']))
                        ->when($data['end_date_to'], fn ($q) => $q->where('end_date', '<=', $data['end_date_to']))),
            ])
            ->searchable()
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
