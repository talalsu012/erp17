<?php

namespace App\Filament\Clusters\Reg\Resources\Faculties\Tables;

use App\Filament\Exports\Reg\FacultyExporter;
use App\Filament\Imports\Reg\FacultyImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class FacultiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label(__('reg.code'))->searchable(),
                TextColumn::make('name_ar')->label(__('reg.name_ar'))->searchable(),
                TextColumn::make('name_en')->label(__('reg.name_en'))->searchable(),
                TextColumn::make('campus.name_ar')->label(__('reg.campus')),
                ToggleColumn::make('is_visible')->label(__('reg.is_enabled'))->toggleable(true),
                ToggleColumn::make('results_published')->label(__('reg.results_published')),
                TextColumn::make('start_reg')->label(__('reg.start_date')),
                TextColumn::make('end_reg')->label(__('reg.end_date')),

            ])
            ->filters([
                TrashedFilter::make(),

            ])
            ->headerActions([
                ImportAction::make()
                    ->label(__('reg.import_faculties'))
                    ->Icon('heroicon-o-document-arrow-up')
                    ->importer(FacultyImporter::class),

                ExportAction::make()
                    ->label(__('reg.export_faculties'))
                    ->color('primary')
                    ->icon('heroicon-o-document-arrow-down')
                    ->exporter(FacultyExporter::class),
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
