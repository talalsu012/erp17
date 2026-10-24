<?php

namespace App\Filament\Clusters\Res\Resources\Resons\Schemas;

use App\Models\Res\Grade;
use App\Models\Res\LtRecom;
use App\Models\Res\Reson;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ResonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('بيانات السبب')
                    ->icon('heroicon-o-tag')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        TextInput::make('code')
                            ->label(__('reg.code'))
                            ->required()
                            ->numeric()
                            ->unique(ignoreRecord: true)
                            ->placeholder('مثال: 2002'),

                        TextInput::make('name_ar')
                            ->label(__('reg.name').' (عربي)')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true, table: Reson::class, column: 'name_ar'),

                        TextInput::make('name_en')
                            ->label(__('reg.name').' (English)')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true, table: Reson::class, column: 'name_en'),

                        Textarea::make('description')
                            ->label('الوصف')
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpanFull(),

                        Toggle::make('is_visible')
                            ->label(__('reg.is_visible'))
                            ->default(true),
                    ]),

                Section::make('أثر السبب على الدرجة')
                    ->description('تُنسخ هذه القيم إلى سجل الدرجة عند اختيار السبب. اترك الثلاثة فارغة ليبقى السبب دون تغيير الدرجة.')
                    ->icon('heroicon-o-calculator')
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        TextInput::make('mark')
                            ->label('الدرجة')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('إن حُددت درجة دون تقدير أو نقاط يُكمَل الناقص من جدول التقديرات.'),

                        TextInput::make('grade')
                            ->label('التقدير')
                            ->maxLength(20)
                            ->datalist(fn () => Grade::query()->distinct()->orderBy('title')->pluck('title')->all()),

                        TextInput::make('point')
                            ->label('النقاط')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(99.99)
                            ->step(0.01),
                    ]),

                Section::make('القرار (التوصية)')
                    ->description('إن اختير قرار فهو يغلب قرار اللائحة لهذا الطالب ما دام السبب قائماً.')
                    ->icon('heroicon-o-scale')
                    ->schema([
                        Select::make('recom_code')
                            ->label('القرار')
                            ->relationship('recom', 'name_ar', fn ($query) => $query->orderBy('code'))
                            ->getOptionLabelFromRecordUsing(fn (LtRecom $record): string => "{$record->name_ar} ({$record->code})")
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('حسب اللائحة'),
                    ]),
            ]);
    }
}
