<?php

namespace App\Filament\Clusters\Reg\Resources\Faculties\Schemas;

use App\Models\Reg\Faculty;
use App\Models\User;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Stichoza\GoogleTranslate\GoogleTranslate;

class FacultiesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make([
                        Select::make('campus_code')->relationship('campus', 'name_ar')->preload()
                            ->searchable()->label(__('reg.campus')),
                        TextInput::make('code')->label(__('reg.code'))->numeric()->maxLength(10)
                            ->required()->unique(ignoreRecord: true),
                        TextInput::make('name_ar')
                            ->label(__('reg.name_ar'))
                            ->maxLength(150)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {

                                // 1. التحقق من أن الحقل ليس فارغاً لتجنب إرسال طلبات فارغة لجوجل
                                if (blank($state)) {
                                    return;
                                }

                                try {
                                    // 2. محاولة الترجمة
                                    $translated = (new GoogleTranslate)
                                        ->setOptions([
                                            'verify' => false, // يخبر cURL بتجاهل فحص شهادة SSL
                                        ])
                                        ->setSource('ar')
                                        ->setTarget('en')
                                        ->translate($state);

                                    // 3. تعيين القيم في حال نجاح الترجمة
                                    $set('name_en', $translated);
                                    $set('slug', Str::slug($translated));

                                } catch (\Exception $e) {
                                    // 4. في حال حدوث أي خطأ (مثل 429 Too Many Requests)
                                    // سيتجاهل النظام الخطأ وتستمر الصفحة بالعمل، ليقوم المستخدم بالإدخال يدوياً
                                    Log::warning('فشلت الترجمة التلقائية للاسم: '.$e->getMessage());
                                }
                            })
                            ->columnSpan('full'),
                        TextInput::make('name_en')
                            ->label(__('reg.name_en'))
                            ->maxLength(150)->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan('full'),

                    ])
                        ->columns(3),
                ])->columnSpan(['lg' => 1]),
                Group::make()->schema([
                    Section::make([

                        Placeholder::make('created_at')->label(__('reg.created_at'))
                            ->content(fn (Faculty $record): ?string => $record->created_at?->diffForHumans())
                            ->hidden(fn (?Faculty $record) => $record === null),
                        Placeholder::make('updated_at')->label(__('reg.updated_at'))
                            ->content(fn (Faculty $record): ?string => $record->updated_at?->diffForHumans())
                            ->hidden(fn (?Faculty $record) => $record === null),

                        Toggle::make('is_visible')->label(__('reg.is_enabled')),
                        Toggle::make('results_published')
                            ->inline()
                            ->label(__('reg.results_published')),

                        ColorPicker::make('primary_hex')
                            ->label(__('reg.primary_hex')),

                        SpatieMediaLibraryFileUpload::make('logo')
                            ->label(__('reg.logo'))
                            ->collection('faculty_logo')
                            ->disk('local')
                            ->getUploadedFileNameForStorageUsing(
                                function (TemporaryUploadedFile $file, $record): string {
                                    $extension = $file->getClientOriginalExtension();

                                    // إذا كان السجل موجوداً (تعديل)
                                    if ($record) {
                                        return 'FAC'.$record->code.'.'.$extension;
                                    }

                                    // إذا كان إنشاء سجل جديد
                                    $code = request()->input('data.code');

                                    return 'FAC'.$code.'.'.$extension;
                                }
                            )
                            ->columnSpan('full'),
                    ])->columns(2),

                    Section::make('معتمد النتائج')
                        ->description('شخص واحد فقط يعتمد نتائج هذه الكلية ويحوّلها إلى السجل الأكاديمي برمزه الشخصي. يظهر هنا من له رمز اعتماد في ملفه.')
                        ->icon('heroicon-m-shield-check')
                        ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                        ->schema([
                            Select::make('results_approver_id')
                                ->label('المعتمد')
                                ->options(fn (): array => User::query()->whereNotNull('approval_code_hash')->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->native(false)
                                ->placeholder('بلا معتمد (لا يمكن اعتماد النتائج)')
                                ->helperText('لإضافة شخص للقائمة: عيّن له رمز اعتماد من صفحة المستخدمين.'),
                        ]),

                    // قسم بيانات إضافية
                    Section::make(__('reg.additional_data'))
                        ->collapsed()
                        ->schema([
                            KeyValue::make('metadata')
                                ->label(__('reg.metadata'))
                                ->keyLabel(__('reg.meta_key'))
                                ->valueLabel(__('reg.meta_value'))
                                ->reorderable(),
                        ]),
                ]),
            ]);
    }
}
