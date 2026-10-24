<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Reg\Department;
use App\Models\User;
use App\Services\Res\FinalizationService;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3) // تقسيم الواجهة إلى 3 أعمدة رئيسية
                    ->schema([

                        // القسم الأول: يأخذ ثلثي المساحة (للبيانات الأساسية والأمان)
                        Grid::make(1)
                            ->schema([
                                Section::make('البيانات الأساسية')
                                    ->description('المعلومات الشخصية وبيانات التواصل للمستخدم')
                                    ->icon('heroicon-m-user-circle') // أيقونة للقسم
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('اسم المستخدم')
                                            ->prefixIcon('heroicon-m-user')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('أدخل الاسم كاملاً'),

                                        TextInput::make('email')
                                            ->label('البريد الإلكتروني')
                                            ->prefixIcon('heroicon-m-envelope')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true) // منع تكرار الإيميل
                                            ->maxLength(255)
                                            ->placeholder('example@domain.com'),
                                    ])->columns(2), // حقلين بجانب بعضهما

                                Section::make('إعدادات الأمان')
                                    ->description('تغيير كلمة المرور وتأكيدها')
                                    ->icon('heroicon-m-lock-closed')
                                    ->schema([
                                        TextInput::make('password')
                                            ->label('كلمة المرور')
                                            ->prefixIcon('heroicon-m-key')
                                            ->password()
                                            ->revealable() // إمكانية إظهار وإخفاء الباسورد بنقرة
                                            ->required(fn (string $context): bool => $context === 'create') // مطلوبة فقط عند إضافة مستخدم جديد
                                            ->dehydrated(fn (?string $state): bool => filled($state)) // لا يتم تحديثها في الداتابيز إلا إذا أدخل قيمة جديدة
                                            ->confirmed() // تتطلب حقل التأكيد
                                            ->maxLength(255),

                                        TextInput::make('password_confirmation')
                                            ->label('تأكيد كلمة المرور')
                                            ->prefixIcon('heroicon-m-check-circle')
                                            ->password()
                                            ->revealable()
                                            ->requiredWith('password')
                                            ->dehydrated(false), // هذا الحقل وهمي للتأكيد فقط، لا يحفظ في الداتابيز
                                    ])->columns(2),

                                Section::make('رمز اعتماد النتائج')
                                    ->description('رمز شخصي سري يخص صاحبه وحده، يُطلب عند اعتماد النتائج وتحويلها إلى السجل الأكاديمي. يُخزَّن مشفّراً ولا يُعرض بعد الحفظ.')
                                    ->icon('heroicon-m-finger-print')
                                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                                    ->schema([
                                        TextInput::make('approval_code_hash')
                                            ->label('رمز الاعتماد')
                                            ->prefixIcon('heroicon-m-key')
                                            ->password()
                                            ->revealable()
                                            ->autocomplete('new-password')
                                            ->helperText('اتركه فارغاً للإبقاء على الرمز الحالي. لا يقل عن '.FinalizationService::MIN_CODE_LENGTH.' خانات ولا يتكرر بين شخصين.')
                                            ->formatStateUsing(fn (): ?string => null)
                                            ->dehydrated(fn (?string $state): bool => filled($state))
                                            ->dehydrateStateUsing(fn (string $state): string => FinalizationService::codeHash($state))
                                            ->rule(fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                                if (mb_strlen(trim((string) $value)) < FinalizationService::MIN_CODE_LENGTH) {
                                                    $fail('رمز الاعتماد يجب ألا يقل عن '.FinalizationService::MIN_CODE_LENGTH.' خانات.');

                                                    return;
                                                }

                                                $taken = User::query()
                                                    ->where('approval_code_hash', FinalizationService::codeHash((string) $value))
                                                    ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey()))
                                                    ->exists();

                                                if ($taken) {
                                                    $fail('رمز الاعتماد مستخدم من شخص آخر، اختر رمزاً فريداً.');
                                                }
                                            }),
                                    ]),
                            ])->columnSpan(['lg' => 2]),

                        // القسم الثاني: يأخذ الثلث الأخير (جانبي للصلاحيات والتاريخ)
                        Grid::make(1)
                            ->schema([
                                Section::make('الصلاحيات والوصول')
                                    ->icon('heroicon-m-shield-check')
                                    ->schema([
                                        Select::make('roles')
                                            ->label('الأدوار (Roles)')
                                            ->relationship('roles', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->native(false) // يعطي تصميم أجمل للـ Select بدلاً من الافتراضي للمتصفح
                                            ->prefixIcon('heroicon-m-shield-exclamation'),

                                        Select::make('faculties')
                                            ->label('الكليات المسموحة')
                                            ->relationship('faculties', 'name_ar')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->native(false)
                                            ->live()
                                            ->prefixIcon('heroicon-m-academic-cap'),

                                        Select::make('departments')
                                            ->label('الأقسام المسموحة')
                                            ->relationship(
                                                name: 'departments',
                                                titleAttribute: 'name_ar',
                                                modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                                    ->when($get('faculties'), fn (Builder $q, array $faculties) => $q->whereIn('reg_departments.faculty_code', $faculties))
                                                    ->orderBy('reg_departments.name_ar'),
                                            )
                                            ->getOptionLabelFromRecordUsing(fn (Department $record): string => $record->name_ar.' — '.$record->faculty?->name_ar)
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->native(false)
                                            ->helperText('اتركه فارغاً للسماح بكل أقسام الكليات المسموحة')
                                            ->prefixIcon('heroicon-m-building-library'),

                                        Select::make('levels')
                                            ->label('المستويات الدراسية المسموحة')
                                            ->relationship('levels', 'name_ar')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->native(false)
                                            ->helperText('اتركه فارغاً للسماح بكل المستويات')
                                            ->prefixIcon('heroicon-m-queue-list'),
                                    ]),

                                Section::make('حالة الحساب')
                                    ->icon('heroicon-m-cog-6-tooth')
                                    ->schema([
                                        DateTimePicker::make('email_verified_at')
                                            ->label('تاريخ توثيق الحساب')
                                            ->prefixIcon('heroicon-m-calendar-days')
                                            ->native(false) // تصميم أجمل للتقويم
                                            ->placeholder('لم يتم التوثيق'),
                                    ]),
                            ])->columnSpan(['lg' => 1]),
                    ])->columnSpan(['lg' => 2]),
            ]);
    }
}
