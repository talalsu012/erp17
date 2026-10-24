<?php

namespace App\Filament\Clusters\Reg\Resources\LtColumns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LtColumnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('parent.name_ar')
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->label(__('reg.parent')),
                TextColumn::make('code')
                    ->label(__('reg.code'))
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable(),
                TextColumn::make('name_ar')->label(__('reg.name'))->searchable()->sortable(),
                TextColumn::make('name_en')->label(__('reg.name_en'))->searchable()->sortable(),
                TextColumn::make('grouped')->label(__('reg.group'))
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable(),

                IconColumn::make('is_visible')->label(__('reg.enabled'))->searchable()->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
