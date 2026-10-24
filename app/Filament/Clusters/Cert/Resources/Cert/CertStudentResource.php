<?php

namespace App\Filament\Clusters\Cert\Resources\Cert;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Clusters\Cert\CertCluster;
use App\Filament\Clusters\Cert\Resources\Cert\CertStudentResource\Pages;
use App\Models\Cert\CertSetting;
use App\Models\Cert\CertStudent;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CertStudentResource extends Resource
{
    protected static ?string $model = CertStudent::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = CertCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $titles = 'cert.students';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->orderBy('created_at', 'DESC');
    }

    public static function getNavigationGroup(): string
    {
        return __('main.certificate');
    }

    public static function getLabel(): ?string
    {
        return __(static::$titles);
    }

    public static function getPluralLabel(): ?string
    {
        return __(static::$titles);
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$titles));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3) // تقسيم الشاشة إلى 3 أعمدة
                    ->columnSpanfull()
                    ->schema([

                        // ==========================================
                        // العمود الرئيسي (يأخذ مساحة 2 من 3)
                        // ==========================================
                        Grid::make(1)->schema([

                            // 1. البيانات الأساسية
                            Section::make(__('البيانات الأساسية'))
                                ->icon('heroicon-o-user')
                                ->columns(2) // حقلين في كل سطر
                                ->schema([
                                    TextInput::make('universityno')
                                        ->label(__('reg.university_no'))
                                        ->maxLength(255)
                                        ->default(null),

                                    TextInput::make('slug')
                                        ->label(__('cert.slug'))
                                        ->required()
                                        ->readOnly(),

                                    TextInput::make('name_ar')
                                        ->label(__('cert.name_ar'))
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, ?string $state) {
                                            if (blank($state)) {
                                                return;
                                            }
                                            // ترجمة الاسم وتوليد الـ Slug
                                            $translated = (new GoogleTranslate)->setSource('ar')->setTarget('en')->translate($state);
                                            $set('name_en', $translated);
                                            $set('slug', Str::slug($translated));
                                        }),

                                    TextInput::make('name_en')
                                        ->label(__('cert.name_en'))
                                        ->required()
                                        ->maxLength(255),
                                ]),

                            // 2. البيانات الأكاديمية
                            Section::make(__('البيانات الأكاديمية'))
                                ->icon('heroicon-o-academic-cap')
                                ->columns(3) // 3 حقول في كل سطر لاستغلال المساحة
                                ->schema([
                                    Select::make('faculty_code')
                                        ->label(__('cert.faculty'))
                                        ->options(Faculty::pluck('name_ar', 'code'))
                                        ->live() // مهم لتحديث الأقسام
                                        ->required(),

                                    Select::make('department_code')
                                        ->label(__('reg.department'))
                                        ->options(fn (Get $get): Collection => Department::query()
                                            ->where('faculty_code', $get('faculty_code'))
                                            ->pluck('name_ar', 'code')
                                        )
                                        ->live()
                                        ->searchable(),

                                    Select::make('special_code')
                                        ->label(__('reg.special'))
                                        ->options(fn (Get $get): Collection => Special::query()
                                            ->where('department_code', $get('department_code'))
                                            ->pluck('name_ar', 'code')
                                        )
                                        ->searchable(),

                                    Select::make('degree_id')
                                        ->label(__('reg.degree'))
                                        ->options(CertSetting::where('groups', 'degree')->pluck('name_ar', 'id'))
                                        ->searchable()
                                        ->required(),

                                    Select::make('grade_id')
                                        ->label(__('reg.grade'))
                                        ->options(CertSetting::where('groups', 'grade')->pluck('name_ar', 'id'))
                                        ->searchable()
                                        ->required(),

                                    Select::make('stady_code')
                                        ->label(__('reg.stady'))
                                        ->options(LtColumn::where('grouped', '2006')->pluck('name_ar', 'code'))
                                        ->required(),
                                ]),
                        ])->columnSpan(['lg' => 2]), // هذا الجزء يأخذ ثلثي الشاشة في الحواسيب

                        // ==========================================
                        // العمود الجانبي (يأخذ مساحة 1 من 3)
                        // ==========================================
                        Grid::make(1)->schema([

                            // 3. معلومات إضافية وتواريخ
                            Section::make(__('معلومات التسجيل'))
                                ->icon('heroicon-o-calendar-days')
                                ->schema([
                                    Select::make('admission_code')
                                        ->label(__('reg.admission'))
                                        ->options(LtColumn::where('grouped', '2001')->pluck('name_ar', 'code'))
                                        ->required(),

                                    Select::make('academic_year_code')
                                        ->label(__('reg.academic_year'))
                                        ->options(LtColumn::where('grouped', '2003')->pluck('name_ar', 'code'))
                                        ->disabled(function () {
                                            /** @var \App\Models\User|null $user */
                                            $user = auth()->user();

                                            return ! $user?->hasFieldPermission('students.academic_year_code');
                                        })
                                        ->dehydrated()
                                        ->required(),

                                    Select::make('nationalty_code')
                                        ->label(__('reg.nationality'))
                                        ->options(LtColumn::where('grouped', '1002')->pluck('name_ar', 'code'))
                                        ->searchable()
                                        ->required(),

                                    Radio::make('gender_code')
                                        ->label(__('reg.gender'))
                                        ->options(LtColumn::where('grouped', '1001')->pluck('name_ar', 'code'))
                                        ->inline()
                                        ->required(),

                                    TextInput::make('meeting_no')
                                        ->label(__('reg.meeting_no'))
                                        ->maxLength(255),

                                    DatePicker::make('alumia_date')
                                        ->label(__('reg.alumia_date'))
                                        ->native(false)
                                        ->displayFormat('d F Y')
                                        ->locale('ar')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state) {
                                                $set('hijri_date', Hijri::MediumDate($state));
                                            }
                                        }),

                                    TextInput::make('hijri_date')
                                        ->label(__('reg.hijri_date')) // يفضل وضع الترجمة هنا بدلاً من النص الثابت
                                        ->readOnly() // الأفضل جعله للقراءة فقط لأنه يُحسب تلقائياً
                                        ->dehydrated(),
                                ]),

                        ])->columnSpan(['lg' => 1]), // هذا الجزء يأخذ ثلث الشاشة كقائمة جانبية

                        // ==========================================
                        // الأسفل (يأخذ العرض بالكامل)
                        // ==========================================
                        Section::make(__('الملاحظات'))
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->columns(2)
                            ->columnSpan('full') // يأخذ العرض بالكامل أسفل الشاشة
                            ->schema([
                                TextInput::make('remarks')
                                    ->label(__('reg.remarks'))
                                    ->maxLength(255),

                                TextInput::make('remarks_en')
                                    ->label(__('reg.remarks_en'))
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('universityno')
                    ->label(__('reg.university_no'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->label(__('reg.name_ar'))
                    // ->size(TextColumnSize::Large)
                    // ->description(fn (CertStudent $record): string => $record->name_en, position: 'above')
                    ->description(fn (CertStudent $record): string => $record->name_en)
                    ->searchable(),

                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('reg.name_en'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('alumia_date')
                    ->label(__('reg.alumia_date'))
                    ->description(fn (CertStudent $record): string => "{$record->Year}  {$record->hijri_date}")
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->label(__('reg.year'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('hijri_date')
                    ->label(__('reg.hijri_date'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('admission.name_ar')
                    ->label(__('reg.admission'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicyear.name_ar')
                    ->label(__('reg.academic_year'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stady.name_ar')
                    ->label(__('reg.stady'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('degree.name_ar')
                    ->label(__('reg.degree'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade.name_ar')
                    ->label(__('reg.grade'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('faculty.name_ar')
                    ->label(__('reg.faculty'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name_ar')
                    ->label(__('reg.department'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('special.name_ar')
                    ->label(__('reg.special_code'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nationalty.name_ar')
                    ->label(__('reg.nationality'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('meeting_no')
                    ->label(__('reg.meeting_no'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('remarks')
                    ->label(__('reg.remarks'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('remarks_en')
                    ->label(__('reg.remarks_en'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('reg.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('reg.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('reg.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),

                    // Add an action to view the certificate
                    // Tables\Actions\Action::make('view_certificate')
                    // ->label('View Certificate')
                    // ->url(fn (CertStudent $record): string => static::getUrl('certificate', ['record' => $record]))
                    // ->icon('heroicon-o-document-text'),

                    RestoreAction::make(),
                    ForceDeleteAction::make(),

                    Action::make('affidavit')
                        ->label(__('الافادة عربي'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn (CertStudent $record): string => route('certificates.affidavit', [
                            'certificate' => $record->id,
                            'lang' => 'ar', // Default to Arabic
                        ]))
                        ->openUrlInNewTab(),

                    Action::make('affidavit')
                        ->label(__('الافادة انجليزي'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn (CertStudent $record): string => route('certificates.affidavit', [
                            'certificate' => $record->id,
                            'lang' => 'en', // Default to English
                        ]))
                        ->openUrlInNewTab(),

                    Action::make('generate')
                        ->label(__('cert.general_en'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn (CertStudent $record): string => route('certificates.generate', [
                            'certificate' => $record->id,
                            'lang' => 'en', // Default to English
                        ]))
                        ->openUrlInNewTab(),

                    Action::make('generateArabic')
                        ->label(__('cert.general_ar'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn (CertStudent $record): string => route('certificates.generate', [
                            'certificate' => $record->id,
                            'lang' => 'ar',
                        ]))
                        ->openUrlInNewTab()
                        ->visible(fn (CertStudent $record): bool => ! empty($record->name_ar)),
                    Action::make('detailsArabic')
                        ->label(__('cert.detail_ar'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn (CertStudent $record): string => route('transcript.details', [
                            'universityNo' => $record->universityno,
                            'lang' => 'ar',
                        ]))
                        ->openUrlInNewTab()
                        ->visible(fn (CertStudent $record): bool => ! empty($record->name_ar)),
                    Action::make('detailsEnglish')
                        ->label(__('cert.detail_en'))
                        ->icon('heroicon-o-document-arrow-up')
                        ->url(fn (CertStudent $record): string => route('transcript.details', [
                            'universityNo' => $record->universityno,
                            'lang' => 'en',
                        ]))
                        ->openUrlInNewTab()
                        ->visible(fn (CertStudent $record): bool => ! empty($record->name_en)),
                ]),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertStudents::route('/'),
            'create' => Pages\CreateCertStudent::route('/create'),
            // 'view' => Pages\ViewCertStudent::route('/{record}'),
            'edit' => Pages\EditCertStudent::route('/{record}/edit'),
            // Register the custom certificate page
            'certificate' => Pages\ViewCertificate::route('/{record}/certificate'),
        ];
    }
}
