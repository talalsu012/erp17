<?php

namespace App\Filament\Clusters\Res\Resources\Resons\Tables;

use App\Filament\Imports\Res\ResonImporter;
use App\Models\Res\LtRecom;
use App\Models\Res\Reson;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('recom'))
            ->defaultSort('code')
            ->columns([
                TextColumn::make('code')
                    ->label(__('reg.code'))
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_ar')
                    ->label('السبب')
                    ->description(fn (Reson $record): ?string => $record->description)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_en')
                    ->label('Name (English)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mark')
                    ->label('الدرجة')
                    ->placeholder('—')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('grade')
                    ->label('التقدير')
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->alignCenter(),

                TextColumn::make('point')
                    ->label('النقاط')
                    ->numeric(2)
                    ->placeholder('—')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('recom.name_ar')
                    ->label('القرار')
                    ->badge()
                    ->color('warning')
                    ->placeholder('حسب اللائحة')
                    ->description(fn (Reson $record): ?string => $record->recom_code ? "رمز {$record->recom_code}" : null),

                IconColumn::make('applies_score')
                    ->label('يغيّر الدرجة')
                    ->state(fn (Reson $record): bool => $record->appliesScore())
                    ->boolean()
                    ->alignCenter(),

                IconColumn::make('is_visible')
                    ->label(__('reg.is_visible'))
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label('استيراد')
                    ->importer(ResonImporter::class),
            ])
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('recom_code')
                    ->label('القرار')
                    ->options(fn (): array => LtRecom::query()->orderBy('code')->pluck('name_ar', 'code')->all()),

                TernaryFilter::make('applies_score')
                    ->label('يغيّر الدرجة')
                    ->placeholder('الكل')
                    ->trueLabel('يفرض درجة/تقديراً/نقاطاً')
                    ->falseLabel('يحفظ السبب فقط')
                    ->queries(
                        true: fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereNotNull('mark')->orWhereNotNull('grade')->orWhereNotNull('point')),
                        false: fn (Builder $query) => $query->whereNull('mark')->whereNull('grade')->whereNull('point'),
                        blank: fn (Builder $query) => $query,
                    ),
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
