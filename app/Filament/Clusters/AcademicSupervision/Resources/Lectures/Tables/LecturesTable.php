<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Tables;

use App\Models\attendances\Lecture;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LecturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('تقرير الحضور')
                    ->formatStateUsing(fn () => 'عرض التقرير') // Custom text
                    ->url(fn (Lecture $record) => route('report.lecture.attendance', $record))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-text') // Document icon
                    ->iconPosition('after') // Icon after text for RTL
                    ->color(Color::Indigo) // Color scheme
                    ->badge() // Display as badge/tag
                    ->extraAttributes([
                        'class' => 'hover:shadow-md transition-all', // Hover effects
                    ]),
                // Grouping Information
                TextColumn::make('grouping.name')
                    ->label(__('Attendance.grouping'))
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->room?->name ?? ''),

                // Subject Information
                TextColumn::make('subject.name_ar')
                    ->label(__('res.subject'))
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->subject->faculty?->name_ar ?? __('reg.faculty')),

                // Staff Information
                TextColumn::make('staff.name')
                    ->label(__('Attendance.staff'))
                    ->sortable()
                    ->searchable(),

                // Day and Time
                TextColumn::make('day_of_week')
                    ->label(__('Attendance.day_of_week'))
                    ->formatStateUsing(fn ($state) => __("Attendance.days.$state"))
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('start_time')
                    ->label(__('Attendance.Time'))
                    ->formatStateUsing(
                        fn ($record) => $record->start_time->format('h:i A').' - '.
                        $record->end_time->format('h:i A')
                    )
                    ->sortable(),

                // Date Range
                TextColumn::make('start_date')
                    ->label(__('Attendance.Date_Range'))
                    ->formatStateUsing(
                        fn ($record) => $record->start_date->format('d/m/Y').' - '.
                        $record->end_date->format('d/m/Y')
                    )
                    ->sortable(),

                // Status Indicator
                IconColumn::make('status')
                    ->label(__('Status'))
                    ->icon(fn (string $state): string => match ($state) {
                        'completed' => 'heroicon-o-check-circle',
                        'ongoing' => 'heroicon-o-clock',
                        'upcoming' => 'heroicon-o-calendar',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'ongoing' => 'warning',
                        'upcoming' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                TrashedFilter::make(),

                // فلتر الكلية (عبر علاقة المادة)
                SelectFilter::make('faculty')
                    ->label(__('reg.faculty'))
                    ->relationship('subject.faculty', 'name_ar')
                    ->columnSpan(2)
                    ->searchable()
                    ->preload(),

                SelectFilter::make('staff_id')
                    ->label(__('Attendance.staff'))
                    ->relationship('staff', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('subject_id')
                    ->label(__('res.subject'))
                    ->relationship('subject', 'name_ar', fn (Builder $query) => $query->with('faculty'))
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $facultyName = $record->faculty?->name_ar ?? __('reg.faculty');

                        return "{$record->name_ar} - ({$facultyName})";
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('day_of_week')
                    ->label(__('Attendance.day_of_week'))
                    ->options([
                        'Monday' => __('Attendance.days.Monday'),
                        'Tuesday' => __('Attendance.days.Tuesday'),
                        'Wednesday' => __('Attendance.days.Wednesday'),
                        'Thursday' => __('Attendance.days.Thursday'),
                        'Friday' => __('Attendance.days.Friday'),
                        'Saturday' => __('Attendance.days.Saturday'),
                        'Sunday' => __('Attendance.days.Sunday'),
                        // ... other days
                    ]),

                Filter::make('current')
                    ->label(__('Current Records'))
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '>=', now())),
            ])
            ->filtersFormColumns(3)
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
