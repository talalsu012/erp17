<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Tables\Filters;

use App\Models\Reg\Department;
use App\Models\Reg\LtColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class StudentFilters
{
    public static function make(): array
    {
        return [

            TrashedFilter::make(),

            // ... داخل دالة $table->filters([])

            FacultyDepartmentFilter::make(),
            SelectFilter::make(__('reg.level'))
                ->relationship('level', 'name_ar')
                ->attribute('level_code')
                ->searchable()->multiple()
                ->preload(),
            // SelectFilter::make('admission_code')
            //     ->label(__('reg.admission'))
            //     ->options(LtColumn::all()
            //         ->where('grouped', '2001')
            //         ->pluck('name_ar', 'code')),

            SelectFilter::make('admission_code')
                ->label(__('reg.admission'))
                // ->required()
                ->options(function () {
                    // 1. جلب الأنواع الرئيسية (الآباء)
                    $parents = LtColumn::query()
                        ->where('grouped', '2001')
                        ->whereNull('parent_code') // تأكد من جلب الآباء فقط
                        ->get();

                    $options = [];

                    foreach ($parents as $parent) {
                        // 2. جلب الأبناء التابعين لهذا الأب
                        $children = LtColumn::query()
                            ->where('grouped', '2001')
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
                }),
            // ->searchable()
            // ->disabled(fn() => !auth()->user()?->hasFieldPermission('students.admission_code'))
            // ->dehydrated(),

            SelectFilter::make('xstatus')
                ->label(__('reg.xstatus'))->multiple()
                ->options(LtColumn::all()
                    ->where('grouped', '2007')
                    ->pluck('name_ar', 'code')),

            SelectFilter::make('gender_code')
                ->label(__('reg.gender'))
                ->options(LtColumn::all()
                    ->where('grouped', '1001')
                    ->pluck('name_ar', 'code')),

            QueryBuilder::make()
                ->constraintPickerColumns(4)
                ->constraints([
                    TextConstraint::make('name_ar')
                        ->label(__('reg.name_ar'))->nullable(),
                    SelectConstraint::make('batch_code')
                        ->label(__('reg.batch'))
                        ->options(LtColumn::all()
                            ->where('grouped', '2003')
                            ->pluck('name_ar', 'code')),

                    SelectConstraint::make('department_code')
                        ->label(__('reg.department'))
                        ->options(Department::forAuthUser()->get()
                            ->pluck('name_ar', 'code')),

                    SelectConstraint::make('academic_year_code')
                        ->label(__('reg.academic_year'))
                        ->options(LtColumn::all()
                            ->where('grouped', '2003')
                            ->pluck('name_ar', 'code')),
                    // StudentAccount
                    SelectConstraint::make('student_account')
                        ->label(__('reg.student_account'))
                        ->options(LtColumn::all()
                            ->where('grouped', '4002')
                            ->pluck('name_ar', 'code')),

                    BooleanConstraint::make('gender_code')->label(__('reg.gender')),
                    NumberConstraint::make('fees')->label(__('reg.fees')),
                    NumberConstraint::make('reg_fees')->label(__('reg.regfees')),
                    NumberConstraint::make('fees1')->label(__('reg.fees1')),
                    NumberConstraint::make('fees2')->label(__('reg.fees2')),
                    NumberConstraint::make('fees3')->label(__('reg.fees3')),

                    NumberConstraint::make('fees5')->label(__('reg.fees5')),

                    NumberConstraint::make('fees6')->label(__('reg.fees6')),
                    NumberConstraint::make('fees7')->label(__('reg.fees7')),
                    NumberConstraint::make('fees8')->label(__('reg.fees8')),
                    SelectConstraint::make('installment_code')
                        ->label(__('reg.Installments'))
                        ->options(LtColumn::all()
                            ->where('grouped', '2005')
                            ->pluck('name_ar', 'code')),

                    NumberConstraint::make('all_fees')->label(__('reg.all_fees')),
                    NumberConstraint::make('xmod')->label(__('reg.xmod')),
                    DateConstraint::make('created_at'),
                ]),
        ];
    }
}
