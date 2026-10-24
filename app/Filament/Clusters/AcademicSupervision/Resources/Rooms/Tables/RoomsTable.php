<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Attendance.room_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('campus.name_ar')
                    ->label(__('reg.campus'))
                    ->searchable(),

                TextColumn::make('capacity')
                    ->label(__('Attendance.room_capacity'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('Attendance.room_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'theoretical' => 'info',
                        'practical' => 'success',
                    }),
            ])
            ->filters([
                //
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
