<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Schemas\Tabs;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Reg\LtGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class BasicInfoTab
{
    public static function make(): Tab
    {
        return Tab::make(__('reg.basic_info'))
            ->schema([

                Grid::make()
                    ->columns(3)
                    ->schema([

                        Section::make()
                            ->schema([
                                TextInput::make('name_ar')
                                    ->label(__('reg.name_ar'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if (! $state) {
                                            return;
                                        }

                                        try {
                                            $translated = (new GoogleTranslate)
                                                ->setOptions([
                                                    'verify' => false, // هذا يخبر cURL بتجاهل فحص شهادة SSL
                                                ])
                                                ->setSource('ar')
                                                ->setTarget('en')
                                                ->translate($state);

                                            $set('name_en', $translated);
                                        } catch (\Exception $e) {
                                            // حماية النظام من الانهيار وتسجيل الخطأ في السجل
                                            Log::warning('فشلت الترجمة التلقائية للاسم: '.$e->getMessage());
                                        }
                                    })
                                    ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.name_ar'))
                                    ->dehydrated(),

                                TextInput::make('name_en')
                                    ->label(__('reg.name_en'))
                                    ->helperText(__('students.name_en_auto_hint'))
                                    ->maxLength(255)
                                    ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.name_en'))
                                    ->dehydrated(),
                            ])
                            ->columnSpan(2),

                        Section::make()
                            ->schema([
                                TextInput::make('university_no')
                                    ->label(__('reg.university_no'))
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(20)
                                    ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.university_no'))
                                    ->dehydrated(),

                                TextInput::make('indexno')
                                    ->label(__('reg.indexno'))
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.indexno'))
                                    ->dehydrated(),
                            ])
                            ->columnSpan(1),
                    ]),

                Fieldset::make()
                    ->columns(3)
                    ->schema([

                        Select::make('faculty_code')
                            ->label(__('reg.faculty'))
                            ->required()
                            ->options(fn () => Faculty::forAuthUser()->pluck('name_ar', 'code'))
                            ->searchable()
                            ->live()
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.faculty_code'))
                            ->dehydrated(),

                        Select::make('department_code')
                            ->label(__('reg.department'))
                            ->options(
                                fn (Get $get): Collection => Department::forAuthUser()
                                    ->where('faculty_code', $get('faculty_code'))
                                    ->pluck('name_ar', 'code')
                            )
                            ->searchable()
                            ->live()
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.department_code'))
                            ->dehydrated(),

                        Select::make('special_code')
                            ->label(__('reg.special'))
                            ->options(
                                fn (Get $get): Collection => Special::query()
                                    ->where('department_code', $get('department_code'))
                                    ->pluck('name_ar', 'code')
                            )
                            ->searchable()
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.special_code'))
                            ->dehydrated(),

                        Select::make('level_code')
                            ->label(__('reg.level'))
                            ->options(fn () => Level::forAuthUser()->pluck('name_ar', 'code'))
                            ->searchable()
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.level_code'))
                            ->dehydrated(),

                        Select::make('stady_code')
                            ->label(__('reg.stady'))
                            ->options(fn () => LtColumn::optionsFor(LtGroup::Stady))
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.stady_code'))
                            ->dehydrated(),

                        Select::make('batch_code')
                            ->label(__('reg.batch'))
                            ->options(fn () => LtColumn::optionsFor(LtGroup::AcademicYear))
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.batch_code'))
                            ->dehydrated(),

                    ]),

                Fieldset::make()
                    ->columns(4)
                    ->schema([
                        Select::make('admission_code')
                            ->label(__('reg.admission'))
                            ->required()
                            ->options(function () {
                                // 1. جلب الأنواع الرئيسية (الآباء)
                                $parents = LtColumn::query()
                                    ->where('grouped', LtGroup::Admission->value)
                                    ->whereNull('parent_code') // تأكد من جلب الآباء فقط
                                    ->get();

                                $options = [];

                                foreach ($parents as $parent) {
                                    // 2. جلب الأبناء التابعين لهذا الأب
                                    $children = LtColumn::query()
                                        ->where('grouped', LtGroup::Admission->value)
                                        ->where('parent_code', $parent->code) // ربط الابن بالأب
                                        ->pluck('name_ar', 'code')
                                        ->toArray();

                                    // 3. إذا كان للأب أبناء، نضعهم في مجموعة، وإذا لم يكن نضعه كخيار مفرد
                                    if (count($children) > 0) {
                                        $options[$parent->name_ar] = $children;
                                    } else {
                                        $options[$parent->code] = $parent->name_ar;
                                    }
                                }

                                return $options;
                            })
                            // ->searchable()
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.admission_code'))
                            ->dehydrated(),
                        Select::make('academic_year_code')
                            ->label(__('reg.academic_year'))
                            ->options(fn () => LtColumn::optionsFor(LtGroup::AcademicYear))
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.academic_year_code'))
                            ->dehydrated(),

                        Select::make('status_code')
                            ->label(__('reg.status_code'))
                            ->options(fn () => LtColumn::optionsFor(LtGroup::Status))
                            ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.status_code'))
                            ->dehydrated(),

                        Select::make('reg_access')
                            ->label('حالة التسجيل للطالب')
                            ->options(\App\Reg\RegAccess::class)
                            ->placeholder('حسب فترة open-regs')
                            ->helperText('فتح التسجيل للطالب منفرداً يتجاوز قفل الفترة، وحجبه يمنعه حتى لو كانت الفترة مفتوحة. الفارغ = حسب فترة open-regs.')
                            ->native(false),

                        Select::make('student_account')
                            ->label(__('reg.student_account'))
                            ->native()
                            ->options(fn () => LtColumn::optionsFor(LtGroup::StudentAccount))
                            ->selectablePlaceholder(true),
                    ]),
            ]);
    }
}
