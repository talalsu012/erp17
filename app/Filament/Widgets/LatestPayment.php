<?php

namespace App\Filament\Widgets;

use App\Models\Reg\Payment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestPayment extends TableWidget
{
    /**
     * ✅ Static properties فقط - هذا هو المطلوب في Filament 3 Widgets
     */
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('reg.payments'))
            ->description('.')
            ->query(
                fn (): Builder => Payment::query()
                    ->with(['student.faculty', 'student.level'])
                    ->whereHas('student')
                    ->latest()
                    ->limit(20)
            )
            ->columns([
                TextColumn::make('university_no')
                    ->label(__('reg.university_no'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ الرقم الجامعي')
                    ->weight(FontWeight::Medium)
                    ->color('primary'),

                TextColumn::make('student.name_ar')
                    ->label(__('reg.name'))
                    ->searchable()
                    ->description(fn ($record) => $record->voucher_no)
                    ->weight(FontWeight::Medium),

                TextColumn::make('amount')
                    ->label(__('reg.amount'))
                    ->formatStateUsing(
                        fn ($state) => '<span class="font-mono font-bold text-success-600 dark:text-success-400">'.
                        number_format($state, 2).' ج.س</span>'
                    )
                    ->html()
                    ->alignRight()
                    ->summarize(
                        Sum::make()
                            ->label('الإجمالي')
                            ->formatStateUsing(
                                fn ($state) => '<span class="font-mono font-bold">'.
                                number_format($state, 2).' ج.س</span>'
                            )
                            ->html()
                    ),

                TextColumn::make('bankdate')
                    ->label(__('reg.bankdate'))
                    ->date('d/m/Y')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('bank')
                    ->label(__('reg.bank'))
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('student.faculty.name_ar')
                    ->label(__('reg.faculty'))
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn ($record) => $record->created_at?->diffForHumans())
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s')
            ->striped()
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('لا توجد مدفوعات')
            ->emptyStateDescription('قم بإضافة مدفوعات جديدة لعرضها هنا');
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true; // تمكين الترقيم لعرض 20 سجلًا في الصفحة
    }

    protected static function getPollingInterval(): ?string
    {
        return '60s';
    }
}
