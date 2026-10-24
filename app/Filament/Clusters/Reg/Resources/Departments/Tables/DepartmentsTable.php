<?php

namespace App\Filament\Clusters\Reg\Resources\Departments\Tables;

use App\Filament\Exports\Reg\DepartmentExporter;
use App\Filament\Imports\Reg\DepartmentImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DepartmentsTable
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

                TextColumn::make('faculty.name_ar')
                    ->label(__('reg.faculty'))->searchable()
                    ->sortable(),
                TextColumn::make('name_en')
                    ->label(__('reg.name_en'))->searchable()->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('is_visible')->toggleable(isToggledHiddenByDefault: true)
                    ->boolean()->label(__('reg.is_visible')),
                IconColumn::make('results_published')
                    ->boolean()->label(__('reg.results_published'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()->label(__('reg.created_at')),
                TextColumn::make('updated_at')->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()->label(__('reg.updated_at')),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label(__('reg.import_departments'))
                    ->importer(DepartmentImporter::class)
                    ->modalWidth('2xl')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-up'),
                ExportAction::make()
                    ->label(__('reg.export_departments'))
                    ->exporter(DepartmentExporter::class)
                    ->color('primary')
                    ->icon('heroicon-o-document-arrow-down'),
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
