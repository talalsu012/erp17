<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Schemas;

use App\Models\Reg\LtColumn;
use App\Reg\Currency;
use App\Reg\LtGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;

/**
 * حقول رسوم الطالب (الطلابية وغير الطلابية والأقساط)، مشتركة بين تبويب الرسوم في الطالب وخطوة تحديد الرسوم في القبول.
 */
class StudentFeesSchema
{
    /** @return list<Fieldset> */
    public static function components(): array
    {
        return [
            Fieldset::make(__('reg.fees_students'))
                ->schema([
                    TextInput::make('reg_fees')
                        ->label(__('reg.regfees'))
                        ->numeric()
                        ->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.reg_fees'))
                        ->dehydrated(),

                    TextInput::make('fees')
                        ->label(__('reg.fees'))
                        ->numeric()->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees'))
                        ->dehydrated(),

                    TextInput::make('fees1')
                        ->label(__('reg.fees1'))
                        ->numeric()->default(0)->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees1'))
                        ->dehydrated(),
                    TextInput::make('fees2')
                        ->label(__('reg.fees2'))
                        ->numeric()->default(0)->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees2'))
                        ->dehydrated(),

                    TextInput::make('fees3')
                        ->label(__('reg.fees3'))
                        ->numeric()->default(0)->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees3'))
                        ->dehydrated(),

                    TextInput::make('fees5')
                        ->label(__('reg.fees5'))
                        ->numeric()->default(0)->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees5'))
                        ->dehydrated(),
                ])->columns(6),

            Fieldset::make(__('reg.nonfees_students'))
                ->schema([
                    TextInput::make('fees6')
                        ->label(__('reg.fees6'))
                        ->numeric()->default(0)->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees6'))
                        ->dehydrated(),

                    TextInput::make('fees7')
                        ->label(__('reg.fees7'))
                        ->numeric()->default(0)->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees7'))
                        ->dehydrated(),

                    TextInput::make('fees8')
                        ->label(__('reg.fees8'))
                        ->numeric()->default(0)->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees8'))
                        ->dehydrated(),

                    Select::make('discount_percent')
                        ->label(__('reg.discount_percent'))
                        ->native()
                        ->required()
                        ->options(LtColumn::optionsFor(LtGroup::DiscountPercent))
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.discount_percent'))
                        ->dehydrated(),

                    ToggleButtons::make('currency')
                        ->inline()
                        ->options(Currency::class)
                        ->label(__('reg.currency'))->default(Currency::SDG)
                        ->required()
                        ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.currency'))
                        ->dehydrated()->columnSpan(2),

                ])->columns(6),

            Fieldset::make(__('reg.Installments'))
                ->schema([
                    Select::make('installment_code')
                        ->label(__('reg.Installments'))
                        ->native(false)
                        ->required()
                        ->options(
                            LtColumn::optionsFor(LtGroup::Installment)
                        )
                        ->live()                    // مهم لتحديث الحقل pay1 فوراً
                        ->dehydrated(),

                    // ->visible(fn(Get $get) => $get('installment_code') === '20050007')   // اختياري: إخفاء الحقل تماماً
                    TextInput::make('fees9')
                        ->label(__('reg.fees9'))
                        ->numeric()
                        ->disabled(fn (Get $get) => $get('installment_code') != '20050007'),

                    TextInput::make('pay1')->label(__('reg.pay1'))->numeric()->required()->default(0)->disabled(),
                    TextInput::make('pay2')->label(__('reg.pay2'))->numeric()->required()->default(0)->disabled(),
                    TextInput::make('pay3')->label(__('reg.pay3'))->numeric()->required()->default(0)->disabled(),
                    TextInput::make('pay4')->label(__('reg.pay4'))->numeric()->required()->default(0)->disabled(),

                ])->columns(6),
        ];
    }
}
