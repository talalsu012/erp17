<?php

namespace App\Filament\Clusters\Res\Resources\SubjectApprovals\Tables;

use App\Models\Reg\Faculty;
use App\Models\Reg\LtColumn;
use App\Models\Res\Subject;
use App\Models\User;
use App\Reg\MarkApprovalStage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectApprovalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.faculty.name_ar')
                    ->label(__('reg.faculty'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.level.name_ar')
                    ->label(__('reg.level'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.name_ar')
                    ->label(__('res.subject'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('batch.name_ar')
                    ->label(__('reg.batch'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('current_stage')
                    ->label(__('res.current_stage'))
                    ->badge()
                    ->icon(fn ($state) => $state?->getIcon()) // اختياري لإظهار الأيقونة
                    ->searchable()
                    ->sortable(),

            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('faculty')
                    ->label(__('reg.faculty'))
                    ->options(Faculty::pluck('name_ar', 'code'))
                    ->searchable()
                    ->multiple()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['values'],
                        fn (Builder $query, $values) => $query->whereHas('subject.faculty', function ($query) use ($values) {
                            $query->whereIn('code', $values);
                        })
                    )),
                SelectFilter::make('subject_code')
                    ->label(__('res.subject'))
                    ->options(Subject::pluck('name_ar', 'code'))
                    ->searchable()
                    ->multiple(),
                SelectFilter::make('batch_code')
                    ->label(__('reg.batch'))
                    ->options(LtColumn::where('grouped', 2003)->pluck('name_ar', 'code'))
                    ->searchable(),
                SelectFilter::make('current_stage')
                    ->label(__('res.current_stage'))
                    ->options(MarkApprovalStage::class)
                    ->searchable(),
                SelectFilter::make('current_user_id')
                    ->label(__('res.current_user'))
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->multiple(),

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
