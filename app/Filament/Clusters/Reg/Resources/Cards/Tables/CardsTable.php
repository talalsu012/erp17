<?php

namespace App\Filament\Clusters\Reg\Resources\Cards\Tables;

use App\Models\Reg\Card;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class CardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('student_photo')
                    ->label(__('reg.avatar'))
                    ->getStateUsing(fn (Card $record): string => $record->student?->photoUrl() ?? Student::defaultPhotoDataUri())
                    ->circular()
                    ->imageSize(55)
                    ->defaultImageUrl(
                        fn (): string => asset('students/avatar/avatar.jpg')
                    ),

                TextColumn::make('status')
                    ->label(__('reg.status_code'))
                    ->badge(),

                TextColumn::make('student.name_ar')
                    ->label(__('reg.name_ar'))
                    ->sortable(),

                TextColumn::make('card_number')
                    ->label(__('reg.card_number'))
                    ->searchable(),

                TextColumn::make('issue_date')
                    ->label(__('reg.issue_date'))
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('expiry_date')
                    ->label(__('reg.expiry_date'))
                    ->date()
                    ->sortable(),

                IconColumn::make('is_visible')
                    ->label(__('reg.is_visible'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label(__('reg.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label(__('reg.updated_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                // تجميع الإجراءات في قائمة منسدلة احترافية
                ActionGroup::make([
                    EditAction::make()
                        ->color('primary')
                        ->icon('heroicon-o-pencil-square'),

                    Action::make('print_card')
                        ->label('طباعة FARGO')
                        ->icon('heroicon-o-printer')
                        ->color('warning')
                        ->tooltip('تصدير البطاقة للطباعة البلاستيكية')
                        ->url(fn ($record) => route('cards.print', $record))
                        ->openUrlInNewTab(),

                    Action::make('previewCard')
                        ->label(__('reg.card'))
                        ->icon('heroicon-o-identification')
                        ->color('success')
                        ->tooltip('معاينة البطاقة')
                        ->modalContent(fn ($record): View => view(
                            'Reg.print-card',
                            ['record' => $record]
                        ))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('reg.close'))
                        ->modalWidth(Width::TwoExtraLarge),
                ])
                    ->label('')
                    ->icon('heroicon-m-cog-8-tooth') // أيقونة ترس احترافية للمجموعة
                    ->color('primary')
                    ->button() // تحويل الأيقونة إلى زر منسدل بشكل أنيق
                    ->tooltip('إجراءات البطاقة'),
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
