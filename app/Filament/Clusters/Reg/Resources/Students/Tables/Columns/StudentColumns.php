<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Tables\Columns;

use Closure;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Js;

class StudentColumns
{
    /**
     * نسخ القيمة عند النقر، ويعمل أيضاً على http (حيث لا يتوفر navigator.clipboard).
     *
     * @return Closure(mixed $state): array<string, string>
     */
    private static function copyOnClick(string $message): Closure
    {
        return fn (mixed $state): array => [
            'class' => 'cursor-pointer',
            'x-on:click.stop' => '(() => {
                const text = '.Js::from((string) $state).';
                const notify = () => $tooltip('.Js::from($message).', { theme: $store.theme, timeout: 2000 });
                if (window.isSecureContext && navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(notify);
                    return;
                }
                const field = document.createElement("textarea");
                field.value = text;
                field.style.position = "fixed";
                field.style.opacity = "0";
                document.body.appendChild(field);
                field.select();
                document.execCommand("copy");
                field.remove();
                notify();
            })()',
        ];
    }

    public static function make(): array
    {
        return [
            //  SpatieMediaLibraryImageColumn::make('avatar')
            //     ->label(__('reg.avatar'))
            //     ->collection('avatar')
            //     ->circular()
            //     ->width(60),

            ImageColumn::make('photo')
                ->label(__('reg.profile_photo'))
                ->getStateUsing(fn (\App\Models\Student $record): string => $record->photoUrl())
                ->imageSize(48)
                ->circular(),

            TextColumn::make('university_no')
                ->label(__('reg.university_no'))
                ->searchable(isIndividual: true, isGlobal: true)
                ->sortable(query: fn (Builder $query, string $direction): Builder => $query->reorder('university_no', $direction))
                ->extraAttributes(self::copyOnClick(__('students.copied_university_no')))
                ->weight(FontWeight::Medium)
                ->color('primary'),

            TextColumn::make('name_ar')
                ->label(__('reg.name_ar'))
                ->searchable(isIndividual: true, isGlobal: true)
                ->sortable(query: fn (Builder $query, string $direction): Builder => $query->reorder('name_ar', $direction))
                ->extraAttributes(self::copyOnClick(__('students.copied_name')))
                ->weight(FontWeight::Medium)
                ->color('purple'),
            TextColumn::make('level.name_ar')
                ->label(__('reg.level'))
                ->sortable(),
            TextColumn::make('x_status.name_ar')
                ->toggleable(isToggledHiddenByDefault: false)
                ->label(__('reg.xstatus')),
            TextColumn::make('StudentAccount.name_ar')
                ->label(__('reg.student_account'))
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('faculty.name_ar')
                ->sortable()
                ->label(__('reg.faculty'))
                ->icon('heroicon-m-academic-cap')
                ->badge()
                ->color('info'),

            TextColumn::make('department.name_ar')
                ->toggleable(isToggledHiddenByDefault: true)
                ->label(__('reg.department'))
                ->icon('heroicon-m-building-office-2'),
            TextColumn::make('all_fees')->label(__('reg.all_fees')),
            TextColumn::make('pay')->label(__('reg.payment')),
            TextColumn::make('xmod')->label(__('reg.xmod')),

            TextColumn::make('fees')->label(__('reg.fees'))->toggleable(isToggledHiddenByDefault: false),
            TextColumn::make('reg_fees')->label(__('reg.regfees'))->toggleable(isToggledHiddenByDefault: false),

            TextColumn::make('fees1')->label(__('reg.fees1'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('fees2')->label(__('reg.fees2'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('fees3')->label(__('reg.fees3'))->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('fees5')->label(__('reg.fees5'))->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('fees6')->label(__('reg.fees6'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('fees7')->label(__('reg.fees7'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('fees8')->label(__('reg.fees8'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('pay1')->label(__('reg.pay1'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('pay2')->label(__('reg.pay2'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('pay3')->label(__('reg.pay3'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('pay4')->label(__('reg.pay4'))->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('stady.name_ar')->label(__('reg.stady'))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('special.name_ar')->label(__('reg.special'))->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('academicyear.name_ar')->label(__('reg.academic_year'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('batch.name_ar')
                ->label(__('reg.batch'))->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('nationaltyno')
                ->label(__('reg.nationaltyno'))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('nationalty.name_ar')
                ->label(__('reg.nationality'))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('email')->label(__('reg.email'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('mobile')->label(__('reg.phone'))->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')->label(__('reg.updated_at'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')->label(__('reg.created_at'))->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('deleted_at')
                ->label(__('reg.deleted_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

        ];
    }
}
