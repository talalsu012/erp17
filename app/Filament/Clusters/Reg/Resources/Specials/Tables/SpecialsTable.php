<?php

namespace App\Filament\Clusters\Reg\Resources\Specials\Tables;

use App\Filament\Exports\Reg\SpecialExporter;
use App\Filament\Imports\Reg\SpecialImporter;
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

class SpecialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label(__('reg.code'))->searchable(),
                TextColumn::make('name_ar')->label(__('reg.name'))->searchable(),
                TextColumn::make('department.name_ar')->label(__('reg.department')),
                ToggleColumn::make('is_visible')->label(__('reg.is_enabled')),
                ToggleColumn::make('results_published')->label(__('reg.published')),
                // TextColumn::make('created_at')->label(__('reg.created_at')),
                TextColumn::make('name_en')->label(__('reg.name_en'))->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label(__('reg.import_specials'))
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-up')
                    ->importer(SpecialImporter::class),

                ExportAction::make()
                    ->label(__('reg.export_specials'))
                    ->color('primary')
                    ->icon('heroicon-o-document-arrow-down')
                    ->exporter(SpecialExporter::class),
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
