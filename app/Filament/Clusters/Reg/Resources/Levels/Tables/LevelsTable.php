<?php

namespace App\Filament\Clusters\Reg\Resources\Levels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('reg.code'))->searchable()
                    ->sortable(),
                TextColumn::make('name_ar')
                    ->label(__('reg.name_ar'))->searchable()
                    ->sortable(),

                TextColumn::make('parent.name_ar')
                    ->label(__('reg.parent'))->searchable()
                    ->sortable(),
                TextColumn::make('name_en')
                    ->label(__('reg.name_en'))->searchable()->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('is_visible')->toggleable(isToggledHiddenByDefault: true)
                    ->boolean()->label(__('reg.is_visible')),

                TextColumn::make('created_at')->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()->label(__('reg.created_at')),
                TextColumn::make('updated_at')->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()->label(__('reg.updated_at')),
            ])
            ->filters([
                SelectFilter::make('code')->label(__('reg.code')),
                SelectFilter::make('name_ar')->label(__('reg.name_ar')),
                SelectFilter::make('name_en')->label(__('reg.name_en')),
                SelectFilter::make('parent.name_ar')->label(__('reg.parent')),
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
