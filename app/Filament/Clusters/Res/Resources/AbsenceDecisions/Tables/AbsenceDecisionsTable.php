<?php

namespace App\Filament\Clusters\Res\Resources\AbsenceDecisions\Tables;

use App\Models\Res\AbsenceDecision;
use App\Reg\AbsenceStatus;
use App\Services\Res\AbsenceService;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AbsenceDecisionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university_no')
                    ->label('الرقم الجامعي')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.name_ar')
                    ->label('الطالب')
                    ->searchable(),

                TextColumn::make('subject.name_ar')
                    ->label('المقرر')
                    ->searchable(),

                TextColumn::make('batch_code')
                    ->label('الدفعة')
                    ->sortable(),

                TextColumn::make('semester')
                    ->label('الفصل'),

                TextColumn::make('absent_sessions')
                    ->label('الغياب')
                    ->formatStateUsing(fn (AbsenceDecision $record): string => "{$record->absent_sessions} / {$record->total_sessions}"),

                TextColumn::make('absence_pct')
                    ->label('النسبة %')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),

                TextColumn::make('decided_at')
                    ->label('تاريخ القرار')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('note')
                    ->label('ملاحظة')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(AbsenceStatus::class)
                    ->default(AbsenceStatus::DeprivationProposed->value),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('اعتماد الحرمان')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('قرار الحرمان من مسؤولية مجلس الكلية ويكون نهائياً، وسيُمنع إدخال درجة الامتحان النهائي لهذا الطالب في المقرر.')
                    ->schema([
                        Textarea::make('note')->label('رقم/تاريخ قرار المجلس')->maxLength(500),
                    ])
                    ->visible(fn (AbsenceDecision $record): bool => $record->status === AbsenceStatus::DeprivationProposed
                        && auth()->user()?->can('update', $record))
                    ->action(fn (AbsenceDecision $record, array $data) => self::run(
                        fn () => app(AbsenceService::class)->approveDeprivation($record, auth()->user(), $data['note'] ?? null),
                        'تم اعتماد الحرمان.',
                    )),

                Action::make('lift')
                    ->label('رفع الحرمان')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('note')->label('سبب رفع الحرمان')->required()->maxLength(500),
                    ])
                    ->visible(fn (AbsenceDecision $record): bool => in_array($record->status, [AbsenceStatus::DeprivationProposed, AbsenceStatus::Deprived], true)
                        && auth()->user()?->can('update', $record))
                    ->action(fn (AbsenceDecision $record, array $data) => self::run(
                        fn () => app(AbsenceService::class)->lift($record, auth()->user(), $data['note']),
                        'تم رفع الحرمان.',
                    )),
            ]);
    }

    private static function run(callable $callback, string $success): void
    {
        try {
            $callback();

            Notification::make()->title($success)->success()->send();
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
