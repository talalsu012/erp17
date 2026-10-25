<?php

namespace App\Filament\Clusters\Reg\Resources\OpenRegs\Schemas;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class OpenRegForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('faculty_code')
                    ->label(__('reg.faculty'))
                    ->required()
                    ->options(fn () => Faculty::forAuthUser()->pluck('name_ar', 'code'))
                    ->searchable()
                    ->live()
                    // ->disabled(fn() => !auth()->user()?->hasFieldPermission('students.faculty_code'))
                    ->dehydrated(),

                Select::make('department_code')
                    ->label(__('reg.department'))
                    ->options(
                        fn (Get $get): Collection => Department::query()
                            ->where('faculty_code', $get('faculty_code'))
                            ->pluck('name_ar', 'code')
                    )
                    ->searchable()
                    ->live()
                    ->placeholder('كل الأقسام')
                    ->helperText('اتركه فارغاً لتشمل الفترة كل أقسام الكلية.')
                    // ->disabled(fn() => !auth()->user()?->hasFieldPermission('students.department_code'))
                    ->dehydrated(),

                Select::make('level_code')
                    ->label(__('reg.level'))
                    ->options(fn () => Level::query()->pluck('name_ar', 'code'))
                    ->searchable()
                    ->placeholder('كل المستويات')
                    ->helperText('اتركه فارغاً لتشمل الفترة كل المستويات.')
                    // ->disabled(fn() => !auth()->user()?->hasFieldPermission('students.level_code'))
                    ->dehydrated(),

                Select::make('batch_code')
                    ->label(__('reg.batch'))
                    ->options(
                        fn () => LtColumn::query()
                            ->where('grouped', '2003')
                            ->pluck('name_ar', 'code')
                    )
                    ->placeholder('كل الدفعات')
                    ->helperText('اتركه فارغاً لتشمل الفترة كل الدفعات.')
                    ->dehydrated(),

                DatePicker::make('start_date')->label(__('reg.start_date'))
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required()
                    ->placeholder('DD/MM/YYYY'),
                DatePicker::make('end_date')->label(__('reg.end_date'))
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required()
                    ->afterOrEqual('start_date')
                    ->helperText('التسجيل مفتوح من البداية إلى النهاية (شاملتين)، وبعدها يُقفل ولا يظهر الطلاب للبنوك. فتح التسجيل وقفله يتمان من هذه الصفحة فقط.')
                    ->placeholder('DD/MM/YYYY'),

            ]);
    }
}
