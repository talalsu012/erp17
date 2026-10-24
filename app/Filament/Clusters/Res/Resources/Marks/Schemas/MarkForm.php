<?php

namespace App\Filament\Clusters\Res\Resources\Marks\Schemas;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Models\Res\Subject;
use App\Reg\AbsStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class MarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(5)
                    ->schema([
                        Select::make('batch_code')
                            ->label(__('reg.batch'))
                            ->required()
                            ->validationMessages([
                                'required' => 'عذراً، يجب عليك اختيار العام الدراسي أولاً لإكمال العملية.',
                            ])
                            ->options(
                                LtColumn::query()
                                    ->where('grouped', 2003)
                                    ->whereNotNull('code')
                                    ->pluck('name_ar', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code))
                            )
                            ->searchable()
                            ->live(),

                        Select::make('faculty_code')
                            ->label(__('reg.faculty'))
                            ->required()
                            ->validationMessages([
                                'required' => 'عذراً، يجب عليك اختيار الكلية أولاً لإكمال العملية.',
                            ])
                            ->options(
                                Faculty::query()
                                    ->whereNotNull('code')
                                    ->selectRaw('code, CONCAT(COALESCE(name_ar, "بدون اسم"), " (", code, ")") as name_with_code')
                                    ->pluck('name_with_code', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code))
                            )
                            ->searchable()
                            ->live()
                            ->columnSpan(2),

                        Select::make('department_code')
                            ->label(__('reg.department'))
                            ->required()
                            ->options(
                                fn (Get $get): Collection => Department::query()
                                    ->whereNotNull('code')
                                    ->where('faculty_code', $get('faculty_code'))
                                    ->pluck('name_ar', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code))
                            )
                            ->searchable()
                            ->dehydrated()
                            ->live(),

                        Select::make('special_code')
                            ->label(__('reg.special'))
                            ->options(
                                fn (Get $get): Collection => Special::query()
                                    ->whereNotNull('code')
                                    ->where('department_code', $get('department_code')) // تم تصحيح المفتاح هنا
                                    ->pluck('name_ar', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code))
                            )
                            ->searchable()
                            ->live()
                            ->dehydrated(),

                        Select::make('level_code')
                            ->label(__('reg.level'))
                            ->options(
                                Level::query()
                                    ->whereNotNull('code')
                                    ->pluck('name_ar', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code))
                            )
                            ->required()
                            ->live(),

                        Select::make('university_no')
                            ->label(__('reg.name_ar'))
                            ->relationship('student', 'name_ar')
                            ->required()
                            ->searchable()
                            ->columnSpan(2),

                        Select::make('subject_code')
                            ->required()
                            ->label(__('res.subject'))
                            ->options(function (Get $get) {
                                if (empty($get('faculty_code')) || empty($get('level_code')) || empty($get('batch_code'))) {
                                    return [];
                                }

                                return Subject::query()
                                    ->whereNotNull('code')
                                    ->where('faculty_code', $get('faculty_code'))
                                    ->when($get('department_code'), fn ($q) => $q->where('department_code', $get('department_code')))
                                    ->when($get('special_code'), fn ($q) => $q->where('special_code', $get('special_code')))
                                    ->where('level_code', $get('level_code'))
                                    ->pluck('name_ar', 'code')
                                    ->map(fn ($name, $code) => (string) ($name ?? $code));
                            })
                            ->columnSpan(2)
                            ->live(),

                        Select::make('examType')
                            ->label(__('res.absent'))
                            ->options(AbsStatus::class)
                            ->live()
                            ->required(),

                        TextInput::make('mark')
                            ->label(__('res.mark'))
                            ->numeric()
                            ->required(),

                        TextInput::make('grade')
                            ->label(__('res.grade'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('point')
                            ->label(__('res.point'))
                            ->required()
                            ->maxLength(255),

                    ])->columnSpanFull(),
            ]);
    }
}
