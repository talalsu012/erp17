<?php

namespace App\Filament\Clusters\Reg\Resources\Payments\Tables;

use App\Filament\Imports\Reg\PaymentImporter;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Payment;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading(__('reg.payments'))
            ->description(__('reg.paymentDescription'))
            ->paginated([10, 25, 50, 100, 'all'])
            ->extremePaginationLinks()
            ->recordClasses(fn (Payment $record) => match ($record->bank) {
                'FIB' => 'draft-post-table-row',
                'ONB' => 'reviewing-post-table-row',
                'FCB' => 'published-post-table-row',
                default => null,
            })
            ->reorderableColumns()
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex()
                    ->sortable(),

                TextColumn::make('university_no')
                    ->label(__('reg.university_no'))
                    ->searchable()
                    ->sortable()
                    ->summarize(Count::make()),

                TextColumn::make('student.name_ar')
                    ->label(__('reg.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('reg.amount'))
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->summarize(
                        Sum::make()
                            ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    )
                    // ->summarize(Range::make())
                    // ->format(fn ($state): string => number_format($state, 2) . ' SDG')
                    ->sortable(),
                TextColumn::make('student.faculty.name_ar')
                    ->label(__('reg.faculty'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('batch.name')
                    ->label(__('reg.batch'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('level.name')
                    ->label(__('reg.level'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('bankdate')
                    ->date()->label(__('reg.bankdate'))
                    ->formatStateUsing(fn ($state) => $state ? date('Y-m-d', strtotime($state)) : null)
                    ->sortable(),
                TextColumn::make('voucher_no')
                    ->label(__('reg.voucher_no'))
                    ->searchable(),
                TextColumn::make('bank')
                    ->label(__('reg.bank'))
                    ->searchable(),
                TextColumn::make('branch')
                    ->label(__('reg.branch'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('currency')
                    ->label(__('reg.currency'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('sys_type')
                    ->label(__('reg.sys_type'))
                    ->toggleable(isToggledHiddenByDefault: true)
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
            ->searchDebounce('950ms')
            ->deferLoading()
            ->striped()
            ->groups([
                Group::make('created_at')
                    ->date()
                    ->label(__('reg.bankdate')),
                Group::make('bank')
                    ->label(__('reg.bank')),
            ])
            ->filters([
                TrashedFilter::make(),
                QueryBuilder::make()
                    ->constraints([

                        SelectConstraint::make('student.admission_code')
                            ->label(__('reg.admission'))
                            ->options(LtColumn::all()
                                ->where('grouped', '2001')
                                ->pluck('name_ar', 'code')),

                        SelectConstraint::make('student.faculty_code')
                            ->label(__('reg.faculty'))
                            ->options(Faculty::all()
                                ->pluck('name_ar', 'code')),
                        SelectConstraint::make('level_code')->label(__('reg.level'))
                            ->options(Level::all()->pluck('name_ar', 'code')),
                        SelectConstraint::make('batch_code')->label(__('reg.batch'))
                            ->options(LtColumn::all()
                                ->pluck('name_ar', 'code')),
                        TextConstraint::make('student.name_ar')
                            ->label(__('reg.name_ar'))
                            ->icon('heroicon-m-user-plus'),
                        SelectConstraint::make('bank')
                            ->label(__('reg.bank'))
                            ->options(
                                Payment::query()
                                    ->select('bank')
                                    ->distinct()
                                    ->pluck('bank', 'bank')
                            )
                            ->icon('heroicon-m-user-plus'),
                        TextConstraint::make('voucher_no')
                            ->label(__('reg.voucher_no'))
                            ->icon('heroicon-m-user-plus'),
                        NumberConstraint::make('amount')
                            ->label(__('reg.amount'))
                            ->icon('heroicon-m-currency-dollar'),
                        DateConstraint::make('bankdate')
                            ->label(__('reg.bankdate'))
                            ->icon('heroicon-m-currency-dollar'),
                    ])
                    ->constraintPickerColumns(2),
            ])->filtersFormColumns(2)
            ->recordActions([

                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),

                    Action::make('printReceipt')
                        ->label('طباعة الإيصال')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        // هذا الجزء هو المسؤول عن عرض التصميم الاحترافي بدلاً من النصوص المتداخلة
                        ->modalContent(fn ($record): View => view(
                            'Reg.print-payment',
                            ['record' => $record],
                        ))
                        ->modalSubmitAction(false) // إخفاء زر "إرسال" لأنه للعرض فقط
                        ->modalCancelActionLabel('إغلاق')
                        ->modalWidth(Width::FourExtraLarge),

                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label(__('reg.import_payments'))
                    ->Icon('heroicon-o-document-arrow-up')
                    ->importer(PaymentImporter::class),

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
