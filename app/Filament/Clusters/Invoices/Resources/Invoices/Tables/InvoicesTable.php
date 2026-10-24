<?php

namespace App\Filament\Clusters\Invoices\Resources\Invoices\Tables;

use App\Reg\InvoiceStatus;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                TextColumn::make('invoice_no')
                    ->label(__('invoice.invoice_no'))
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable()
                    ->copyable()
                    ->copyMessage('تم نسخ الرقم الفاتورة')
                    ->weight(FontWeight::ExtraBold)
                    ->color('primary'),

                TextColumn::make('name_ar')
                    ->label(__('invoice.name_ar'))
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->limit(30),

                TextColumn::make('amount')
                    ->label(__('invoice.amount'))
                    // ->money('SDG')
                    ->sortable(),

                TextColumn::make('faculty.name_ar')
                    ->label(__('invoice.faculty_code'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(__('invoice.status'))
                    ->badge(),

                IconColumn::make('bank_pay')
                    ->label(__('invoice.bank_pay'))
                    ->boolean(),

                TextColumn::make('bank')
                    ->label(__('invoice.bank'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('voucher_no')
                    ->label(__('invoice.voucher_no'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bankdate')
                    ->label(__('invoice.bankdate'))
                    ->dateTime('Y-m-d')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('due_date')
                    ->label(__('invoice.due_date'))
                    ->dateTime('Y-m-d')
                    ->sortable(),

                TextColumn::make('expire_at')
                    ->label(__('invoice.expire_at'))
                    ->dateTime('Y-m-d')
                    ->color(
                        fn ($record) => $record->expire_at && now()->greaterThan($record->expire_at)
                        ? 'danger'
                        : null
                    ),

                TextColumn::make('created_at')
                    ->label(__('invoice.created_at'))
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            ->filters([

                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->label(__('invoice.status'))
                    ->options(InvoiceStatus::class),

                SelectFilter::make('faculty_code')
                    ->label(__('invoice.faculty_code'))
                    ->relationship('faculty', 'name_ar'),

                SelectFilter::make('bank_pay')
                    ->label(__('invoice.bank_pay'))
                    ->options([
                        1 => __('invoice.bank_pay_options.bank'),
                        0 => __('invoice.bank_pay_options.non_bank'),
                    ]),

            ])

            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon(Heroicon::PencilSquare)
                        ->hiddenLabel(),
                    Action::make('print')
                        ->hiddenLabel()
                        ->icon('heroicon-o-printer')
                        ->url(fn ($record) => route('invoice.print', $record))
                        ->openUrlInNewTab()
                        ->color('success'),
                ])
                    ->dropdownPlacement('top-start')
                    ->buttonGroup(),
                // ->visible(fn($record) => $record->bank_pay == 0), // إ
            ], position: RecordActionsPosition::BeforeColumns)

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
