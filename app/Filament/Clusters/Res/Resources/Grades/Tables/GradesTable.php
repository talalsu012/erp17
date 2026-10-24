<?php

namespace App\Filament\Clusters\Res\Resources\Grades\Tables;

use App\Filament\Imports\Res\GradeImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('res.title'))
                    ->searchable(),
                TextColumn::make('titlear')
                    ->label(__('res.titlear'))
                    ->searchable(),
                TextColumn::make('point')
                    ->label(__('res.point'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('upper')
                    ->label(__('res.upper'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lower')
                    ->label(__('res.lower'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('plans.name_ar')
                    ->label(__('res.plan'))
                    ->sortable()
                    ->searchable(),
                // TextColumn::make('stady.name')
                // ->label(__('reg.stady')),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                // CreateAction::make(),
                ImportAction::make()
                    ->importer(GradeImporter::class)
                    ->label('Import')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('primary')
                    ->size('md'),
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
