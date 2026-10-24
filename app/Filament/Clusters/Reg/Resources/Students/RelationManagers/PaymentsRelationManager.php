<?php

namespace App\Filament\Clusters\Reg\Resources\Students\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('university_no')
            ->heading(__('reg.payments'))
            ->columns([
                TextColumn::make('batch.name')
                    ->label(__('reg.batch'))
                    ->sortable(),
                TextColumn::make('level.name')
                    ->label(__('reg.level'))
                    ->sortable(),

                TextColumn::make('amount')
                    ->numeric()->label(__('reg.amount'))
                    ->summarize(Sum::make())
                    ->sortable(),

                TextColumn::make('voucher_no')
                    ->label(__('reg.voucher_no'))
                    ->searchable(),

                TextColumn::make('bankdate')
                    ->date()->label(__('reg.bankdate'))
                    ->sortable(),
                TextColumn::make('bank')
                    ->label(__('reg.bank'))
                    ->searchable(),
                TextColumn::make('branch')
                    ->label(__('reg.branch'))
                    ->searchable(),
                TextColumn::make('currency')
                    ->label(__('reg.currency'))
                    ->searchable(),
                TextColumn::make('sys_type')
                    ->label(__('reg.deposit'))
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('reg.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // bank

            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
