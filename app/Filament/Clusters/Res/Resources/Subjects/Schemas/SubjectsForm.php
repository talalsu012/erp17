<?php

namespace App\Filament\Clusters\Res\Resources\Subjects\Schemas;

use App\Models\Reg\Department;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SubjectsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // =============================================
                // القسم الأول: المعلومات الأساسية للمادة
                // =============================================
                Fieldset::make(__('res.subject'))
                    ->label(__('res.subject_info'))
                    ->extraAttributes([
                        'class' => 'subject-fieldset relative overflow-hidden border-t-4 border-t-primary-500',
                    ])
                    ->columns([
                        'xl' => 1,
                    ])
                    ->schema([

                        // سطر الكود والرؤية
                        Grid::make(7)
                            ->schema([
                                TextInput::make('code')
                                    ->label(__('reg.code'))
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(10)
                                    ->required()
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->placeholder('مثال: 1001202')
                                    ->helperText(__('res.code_helper'))
                                    ->ColumnSpan(3),
                                // للتحديث يبجب ان يمرره الفريد اريد المساعدة
                                TextInput::make('pin')
                                    ->label(__('res.pin'))
                                    // ->unique(ignoreRecord: true)
                                    ->maxLength(10)
                                    ->required()
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->placeholder('مثال: MATH101')
                                    ->helperText(__('res.pin_helper'))
                                    ->ColumnSpan(3),

                                // حقل فارغ للتوازن
                                // \Filament\Forms\Components\Placeholder::make('spacer1')
                                //     ->hiddenLabel()
                                //     ->content(''),

                                Toggle::make('is_visible')
                                    ->label(__('reg.is_visible'))
                                    ->default(true)
                                    ->inline(false)
                                    ->onIcon('heroicon-o-eye')
                                    ->offIcon('heroicon-o-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger'),

                                Select::make('parent_code')
                                    ->label(__('res.parent_subject'))
                                    ->relationship('parent', 'name_ar', fn (Builder $query) => $query->whereNull('parent_code'))
                                    ->searchable()
                                    ->placeholder(__('res.select_parent'))
                                    ->prefixIcon('heroicon-o-folder')
                                    ->helperText(__('res.parent_helper'))
                                    ->ColumnSpan(3),

                            ]),

                        // سطر الأسماء
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name_ar')
                                    ->label(__('reg.name_ar'))
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-language')
                                    ->placeholder('الاسم بالعربية')
                                    ->extraInputAttributes(['dir' => 'rtl']),

                                TextInput::make('name_en')
                                    ->label(__('reg.name_en'))
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-language')
                                    ->placeholder('English Name')
                                    ->extraInputAttributes(['dir' => 'ltr']),
                            ]),

                        // خط فاصل
                        \Filament\Forms\Components\Placeholder::make('divider1')
                            ->hiddenLabel()
                            ->content('')
                            ->extraAttributes(['class' => 'border-t border-gray-200 my-4']),

                        // سطر التصنيفات الرئيسية
                        Grid::make(2)
                            ->schema([

                                Select::make('faculty_code')
                                    ->label(__('reg.faculty'))
                                    ->required()
                                    // ->options(\App\Models\Reg\Faculty::query()
                                    //     ->orderBy('name_ar')
                                    //     ->pluck('name_ar', 'code'))
                                    ->options(fn () => \App\Models\Reg\Faculty::forAuthUser()->pluck('name_ar', 'code'))
                                    ->live()
                                    ->native(false)
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-building-library')
                                    ->afterStateUpdated(fn (callable $set) => $set('department_code', null)),

                                Select::make('department_code')
                                    ->label(__('reg.department'))
                                    ->options(
                                        fn (Get $get): Collection => Department::forAuthUser()
                                            ->when(
                                                $get('faculty_code'),
                                                fn ($query, $facultyCode) => $query->where('faculty_code', $facultyCode)
                                            )
                                            ->orderBy('name_ar')
                                            ->pluck('name_ar', 'code')
                                    )
                                    ->live()
                                    ->searchable()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-building-office')
                                    // ->placeholder(__('res.select_department'))
                                    ->disabled(fn (Get $get): bool => ! $get('faculty_code')),

                                Select::make('special_code')
                                    ->label(__('reg.special'))
                                    ->options(
                                        fn (Get $get): Collection => \App\Models\Reg\Special::query()
                                            ->when(
                                                $get('department_code'),
                                                fn ($query, $departmentCode) => $query->where('department_code', $departmentCode)
                                            )
                                            ->orderBy('name_ar')
                                            ->pluck('name_ar', 'code')
                                    )
                                    ->live()
                                    ->searchable()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-academic-cap')
                                    ->visible(fn (Get $get): bool => $get('department_code') !== null)
                                    ->disabled(fn (Get $get): bool => ! $get('department_code')),
                            ]),

                        // سطر المستوى والفصل الدراسي
                        Grid::make(2)
                            ->schema([
                                Select::make('level_code')
                                    ->label(__('reg.level'))
                                    ->required()
                                    ->options(Level::forAuthUser()
                                        ->orderBy('code')
                                        ->pluck('name_ar', 'code'))
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-academic-cap')
                                    // ->placeholder(__('res.select_level'))
                                    ->native(false),

                                Select::make('semester_id')
                                    ->label(__('res.semester'))
                                    ->required()
                                    ->native(false)
                                    ->options(collect(\App\Services\Res\RegulationProfile::current()->subjectSemesters())
                                        ->mapWithKeys(fn (int $semester): array => [(string) $semester => match ($semester) {
                                            1 => __('res.first_semester'),
                                            2 => __('res.second_semester'),
                                            default => \App\Reg\Semester::Summer->getLabel(),
                                        }])
                                        ->all())
                                    ->selectablePlaceholder(false)
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->default((string) \App\Services\Res\RegulationProfile::current()->defaultSemester()),
                            ]),
                    ]),
                // ->collapsible(),

                // =============================================
                // القسم الثاني: معلومات الامتحان والنتائج
                // =============================================
                Fieldset::make(__('res.ResultExam'))
                    ->label(__('res.exam_info'))
                    ->extraAttributes([
                        'class' => 'exam-fieldset relative overflow-hidden border-t-4 border-t-warning-500 mt-6',
                    ])
                    ->columns([
                        'xl' => 1,
                    ])
                    ->schema([

                        // سطر الساعات والامتحان
                        Grid::make(2)
                            ->schema([
                                TextInput::make('crch')
                                    ->label(__('res.crch'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->prefixIcon('heroicon-o-clock')
                                    ->placeholder('مثال: 3')
                                    ->suffix(__('res.hours')),

                                TextInput::make('exam')
                                    ->label(__('res.exam'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->prefixIcon('heroicon-o-document-text')
                                    ->placeholder('مثال: 50')
                                    ->suffix(__('res.degree')),
                            ]),

                        // سطر الخطة والنوع
                        Grid::make(2)
                            ->schema([
                                Select::make('plan')
                                    ->label(__('res.plan'))
                                    ->options(LtColumn::query()
                                        ->where('group_slug', 'plan_code')
                                        ->orderBy('code')
                                        ->pluck('name_ar', 'code'))
                                    ->native(false)
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-clipboard-document-list'),
                                // ->placeholder(__('res.select_plan')),

                                Select::make('type')
                                    ->label(__('res.type'))
                                    ->options(LtColumn::query()
                                        ->where('group_slug', 'type_code')
                                        ->orderBy('code')
                                        ->pluck('name_ar', 'code'))
                                    ->native(false)
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-tag'),
                                // ->placeholder(__('res.select_type'))
                            ]),

                        // سطر الحالة
                        Grid::make(1)
                            ->schema([
                                // TextInput::make('status')
                                //     ->label(__('res.status'))
                                //     ->default(0)
                                //     ->numeric()
                                //     ->minValue(0)
                                //     ->maxValue(1)
                                //     ->prefixIcon('heroicon-o-check-badge')
                                //     ->helperText(__('res.status_helper'))
                                //     ->hint(__('res.status_hint'))
                                //     ->hintIcon('heroicon-o-information-circle'),

                                Toggle::make('status')
                                    ->label(__('res.status'))
                                    ->default(true)
                                    ->inline(false)
                                    ->onIcon('heroicon-o-eye')
                                    ->offIcon('heroicon-o-eye-slash')
                                    ->onColor('success')
                                    ->offColor('danger')

                                    // ->prefixIcon('heroicon-o-check-badge')
                                    ->helperText(__('res.status_helper'))
                                    ->hint(__('res.status_hint'))
                                    ->hintIcon('heroicon-o-information-circle'),

                            ]),
                    ]),
                // ->collapsible()
                // ->collapsed(),

                // // =============================================
                // // القسم الثالث: معلومات إضافية (اختياري)
                // // =============================================
                // Fieldset::make(__('res.additional_info'))
                //     ->label(__('res.additional_info'))
                //     ->extraAttributes([
                //         'class' => 'additional-fieldset border border-gray-200 mt-4'
                //     ])
                //     ->schema([
                //         Grid::make(2)
                //             ->schema([
                //                 TextInput::make('credit_hours')
                //                     ->label(__('res.credit_hours'))
                //                     ->numeric()
                //                     ->default(0)
                //                     ->prefixIcon('heroicon-o-calculator'),

                //                 TextInput::make('lecture_hours')
                //                     ->label(__('res.lecture_hours'))
                //                     ->numeric()
                //                     ->default(0)
                //                     ->prefixIcon('heroicon-o-presentation-chart-line'),

                //                 TextInput::make('lab_hours')
                //                     ->label(__('res.lab_hours'))
                //                     ->numeric()
                //                     ->default(0)
                //                     ->prefixIcon('heroicon-o-beaker'),
                //             ]),

                //         Grid::make(2)
                //             ->schema([
                //                 Textarea::make('notes_ar')
                //                     ->label(__('res.notes_ar'))
                //                     ->rows(3)
                //                     ->ColumnSpanFull()
                //                     ->extraInputAttributes(['dir' => 'rtl']),

                //                 Textarea::make('notes_en')
                //                     ->label(__('res.notes_en'))
                //                     ->rows(3)
                //                     ->ColumnSpanFull()
                //                     ->extraInputAttributes(['dir' => 'ltr']),
                //             ]),
                //     ])
                //     //  ->collapsible()
                //     // ->collapsed()
            ])

            // =============================================
            // إضافة بعض الخصائص العامة للسكيمة
            // =============================================
            ->extraAttributes([
                'class' => 'subject-schema p-4 bg-gray-50 rounded-xl',
            ])
            ->statePath('data');
    }

    // public static function configure(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([

    //             Fieldset::make(__('res.subject'))
    //                 ->schema([

    //                     TextInput::make('code')->label(__('reg.code'))
    //                         ->unique(ignoreRecord: true)->maxLength(10)->required(),

    //                     TextInput::make('name_ar')
    //                         ->required()
    //                         ->label(__('reg.name_ar'))
    //                         ->maxLength(255)
    //                         ->columnSpan(2),

    //                     TextInput::make('name_en')
    //                         ->required()
    //                         ->label(__('reg.name_en'))
    //                         ->maxLength(255)
    //                         ->columnSpan(2),

    //                     Toggle::make('is_visible')
    //                         ->label(__('reg.is_visible'))
    //                         ->default(true),

    //                    Select::make('parent_code')
    //                         ->label(__('res.parent_subject'))
    //                         ->relationship('parent', 'name', fn (Builder $query) => $query->where('parent_code', null))
    //                         ->searchable()
    //                         ->placeholder('Select parent category')
    //                         ->columnSpan(2),

    //                         Select::make('faculty_code')
    //                         ->label(__('reg.faculty'))->required()
    //                         ->options(\App\Models\Reg\Faculty::get()->pluck('name_ar', 'code'))
    //                         ->live()->native()->searchable()
    //                         ->columnSpan(2),

    //                     Select::make('department_code')
    //                        ->label(__('reg.department'))
    //                         ->options(fn (Get $get): Collection => Department::query()
    //                             ->where('faculty_code', $get('faculty_code'))
    //                             ->pluck('name_ar', 'code'))->live()->searchable()
    //                             ->columnSpan(2),

    //                             Select::make('level_code')->required()
    //                             ->label(__('reg.level'))
    //                             ->options(Level::all()->pluck('name_ar', 'code'))
    //                             ->searchable()
    //                             ->preload(),

    //                             Select::make('semester_id')->required()
    //                             ->label(__('res.semester'))
    //                             ->native()
    //                             ->options([
    //                                 '1' => __('res.first_semester'),
    //                                 '2' => __('res.second_semester'),

    //                             ])
    //                             ->selectablePlaceholder(false),

    //                 ])->columns(5),
    //             Fieldset::make(__('res.ResultExam'))
    //                 ->schema([

    //                     TextInput::make('crch')->label(__('res.crch'))
    //                         ->required()->numeric(),

    //                     TextInput::make('exam')->label(__('res.exam'))
    //                         ->required()->numeric(),

    //                     Select::make('plan')->label(__('res.plan'))
    //                         ->options(LtColumn::all()
    //                             ->where('group_slug', 'plan_code')
    //                             ->pluck('name_ar', 'code'))
    //                         ->native(false),

    //                     Select::make('type')->label(__('res.type'))
    //                         ->options(LtColumn::all()
    //                             ->where('group_slug', 'type_code')
    //                             ->pluck('name_ar', 'code'))
    //                         ->native(false),

    //                     TextInput::make('status')->label(__('res.status'))
    //                         ->default(0)->numeric(),

    //                 ])->columns(5),

    //         ]);
    // }
}
