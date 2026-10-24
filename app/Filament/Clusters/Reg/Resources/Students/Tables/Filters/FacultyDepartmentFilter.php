<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Tables\Filters;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Special;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * فلتر الكلية والقسم والتخصص المتسلسل.
 */
class FacultyDepartmentFilter
{
    public static function make(): Filter
    {
        return Filter::make('faculty_and_department')
            ->form([
                Select::make('faculty_code')
                    ->label(__('reg.faculty'))
                    ->options(fn () => Faculty::forAuthUser()->pluck('name_ar', 'code'))
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->live(), // هذا السطر ضروري جداً لتحديث قائمة الأقسام فور الاختيار

                Select::make('department_code')
                    ->label(__('reg.department'))
                    ->options(function (Get $get) {
                        // المتغير هنا سيكون مصفوفة (Array) بسبب خاصية multiple
                        $facultyCodes = $get('faculty_code');

                        if (empty($facultyCodes)) {
                            // إذا لم يتم تحديد كلية، نعرض كل الأقسام
                            // (أو يمكنك إرجاع مصفوفة فارغة collect() إذا أردت إخفاءها)
                            return [];
                        }

                        // جلب الأقسام التابعة للكليات المختارة فقط
                        return Department::forAuthUser()->whereIn('faculty_code', $facultyCodes)
                            ->pluck('name_ar', 'code');
                    })
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->live(),

                Select::make('special_code')
                    ->label(__('reg.special'))
                    ->options(function (Get $get) {
                        $departmentCodes = $get('department_code');

                        if (empty($departmentCodes)) {
                            return [];
                        }

                        return Special::query()->whereIn('department_code', $departmentCodes)
                            ->pluck('name_ar', 'code');
                    })
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ])
            ->query(
                // تنفيذ الاستعلام (البحث) بناءً على القيم المختارة في الفلتر
                fn (Builder $query, array $data): Builder => $query
                    ->when(
                        $data['faculty_code'] ?? null,
                        fn (Builder $query, $codes): Builder => $query->whereIn('faculty_code', $codes),
                    )
                    ->when(
                        $data['department_code'] ?? null,
                        fn (Builder $query, $codes): Builder => $query->whereIn('department_code', $codes),
                    )
                    ->when(
                        $data['special_code'] ?? null,
                        fn (Builder $query, $codes): Builder => $query->whereIn('special_code', $codes),
                    ))->columns(3)->columnSpanFull();
    }
}
