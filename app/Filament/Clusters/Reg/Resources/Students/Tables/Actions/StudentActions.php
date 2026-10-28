<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Tables\Actions;

use App\Actions\Students\BulkUpdateStudents;
use App\Actions\Students\ResetStudentAccount;
use App\Filament\Clusters\Reg\Resources\Students\StudentsResource;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Reg\LtGroup;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;

class StudentActions
{
    // أزرار السجل الفردي
    public static function getRecordActions(): array
    {
        return [
            \Filament\Actions\ActionGroup::make([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make()
                    ->label('تحويل للأرشيف')
                    ->modalHeading('تحويل للأرشيف')
                    ->successNotificationTitle('تم التحويل للأرشيف'),
                \Filament\Actions\RestoreAction::make()
                    ->label('استعادة من الأرشيف')
                    ->modalHeading('استعادة من الأرشيف')
                    ->successNotificationTitle('تمت الاستعادة من الأرشيف'),

                Action::make('resetAccount')
                    ->authorize('resetAccount')
                    ->label(__('students.reset_account'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('students.reset_account'))
                    ->modalDescription(__('students.reset_account_confirm'))
                    ->modalSubmitActionLabel(__('students.reset_account_submit'))
                    ->visible(
                        // 🔴 التعديل هنا بإضافة المسار الكامل
                        fn (\App\Models\Student $record): bool => filled($record->university_no)
                    )
                    ->action(function (\App\Models\Student $record, ResetStudentAccount $resetStudentAccount): void {
                        try {
                            $deleted = $resetStudentAccount->execute($record);
                        } catch (\Throwable $e) {
                            report($e);

                            Notification::make()
                                ->danger()
                                ->title(__('students.reset_account_failed'))
                                ->body($e->getMessage())
                                ->send();

                            return;
                        }

                        if (! $deleted) {
                            Notification::make()
                                ->warning()
                                ->title(__('students.account_not_found'))
                                ->body("الرقم الجامعي: {$record->university_no}")
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title(__('students.reset_account_done'))
                            ->send();
                    }),

                self::registrationAccessAction('openRegistration', 'فتح التسجيل للطالب', 'heroicon-o-lock-open', 'success', \App\Reg\RegAccess::Open),
                self::registrationAccessAction('blockRegistration', 'حجب التسجيل عن الطالب', 'heroicon-o-lock-closed', 'danger', \App\Reg\RegAccess::Closed),
                self::registrationAccessAction('resetRegistration', 'إعادة التسجيل حسب الفترة', 'heroicon-o-arrow-path', 'gray', null),

                Action::make('activities')
                    ->url(fn ($record) => StudentsResource::getUrl('activities', ['record' => $record])),
            ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->size(Size::Small)
                ->color('primary')
                ->button(),
        ];
    }

    /**
     * إجراء فتح التسجيل للطالب (فردياً) أو حجبه أو إعادته لفترة open-regs.
     */
    private static function registrationAccessAction(string $name, string $label, string $icon, string $color, ?\App\Reg\RegAccess $access, bool $bulk = false): Action|BulkAction
    {
        $action = $bulk ? BulkAction::make($name)->accessSelectedRecords() : Action::make($name);

        return $action
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->action(function (\App\Services\Reg\RegistrationAccessService $service, ?\App\Models\Student $record = null) use ($access, $bulk, $action): void {
                $students = $bulk ? $action->getSelectedRecords() : [$record];

                foreach ($students as $student) {
                    $service->grant($student, $access);
                }

                Notification::make()->success()->title('تم تحديث حالة التسجيل')->send();
            });
    }

    public static function getBulkActions(): array
    {
        return [
            \Filament\Actions\BulkActionGroup::make([
                \Filament\Actions\DeleteBulkAction::make()
                    ->label('تحويل للأرشيف')
                    ->modalHeading('تحويل المحدد للأرشيف')
                    ->successNotificationTitle('تم التحويل للأرشيف'),
                \Filament\Actions\ForceDeleteBulkAction::make(),
                \Filament\Actions\RestoreBulkAction::make()
                    ->label('استعادة من الأرشيف')
                    ->modalHeading('استعادة المحدد من الأرشيف')
                    ->successNotificationTitle('تمت الاستعادة من الأرشيف'),
                self::registrationAccessAction('openRegistrationBulk', 'فتح التسجيل للمحدَّدين', 'heroicon-o-lock-open', 'success', \App\Reg\RegAccess::Open, bulk: true),
                self::registrationAccessAction('blockRegistrationBulk', 'حجب التسجيل عن المحدَّدين', 'heroicon-o-lock-closed', 'danger', \App\Reg\RegAccess::Closed, bulk: true),
                self::registrationAccessAction('resetRegistrationBulk', 'إعادة التسجيل حسب الفترة', 'heroicon-o-arrow-path', 'gray', null, bulk: true),
            ]),

            BulkAction::make('updateStudents')
                ->authorize('bulkUpdate')
                ->label(__('reg.updateStudent'))
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->modalWidth(Width::SixExtraLarge)
                ->accessSelectedRecords()
                ->action(function (BulkAction $action, array $data, BulkUpdateStudents $bulkUpdateStudents): void {
                    $records = $action->getSelectedRecords();

                    if (empty($bulkUpdateStudents->selectedFields($data))) {
                        \Filament\Notifications\Notification::make()
                            ->title(__('students.bulk_select_one_field'))
                            ->warning()
                            ->send();

                        return;
                    }

                    $bulkUpdateStudents->execute($records, $data);

                    \Filament\Notifications\Notification::make()
                        ->title(__('reg.updated_successfully'))
                        ->success()
                        ->send();
                })
                ->form([
                    Tabs::make('Bulk Update')
                        ->tabs([
                            Tabs\Tab::make(__('reg.basic_info'))
                                ->icon('heroicon-o-academic-cap')
                                ->schema([
                                    CheckboxList::make('academic_fields')
                                        ->label(__('students.bulk_academic_fields'))
                                        ->options([
                                            'faculty_code' => __('reg.faculty'),
                                            'department_code' => __('reg.department'),
                                            'special_code' => __('reg.special'),
                                            'level_code' => __('reg.level'),
                                            'stady_code' => __('reg.stady'),
                                            'batch_code' => __('reg.batch'),
                                            'academic_year_code' => __('reg.academic_year'),
                                            'status_code' => __('reg.status_code'),
                                            'admission_code' => __('reg.admission'),
                                            'nationality_code' => __('reg.nationality'),
                                            'gender_code' => __('reg.gender'),
                                            'student_account' => __('reg.student_account'),
                                            'reg_access' => 'حالة التسجيل للطالب',
                                        ])
                                        ->columns(4)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $fields = (array) $state;

                                            // إذا حدد التخصص، نجبر إضافة القسم والكلية تلقائياً للقائمة
                                            if (in_array('special_code', $fields)) {
                                                $fields = array_unique(array_merge($fields, ['department_code', 'faculty_code']));
                                            }
                                            // إذا حدد القسم، نجبر إضافة الكلية للقائمة
                                            elseif (in_array('department_code', $fields)) {
                                                $fields = array_unique(array_merge($fields, ['faculty_code']));
                                            }

                                            $set('academic_fields', array_values($fields));
                                        }),

                                    Grid::make(3)
                                        ->schema([
                                            // ------------------ 1. الكلية ------------------
                                            Select::make('faculty_code')
                                                ->label(__('reg.faculty'))
                                                ->options(fn () => Faculty::forAuthUser()->pluck('name_ar', 'code'))
                                                ->live()
                                                ->searchable()
                                                // إلزام ملء الكلية إذا اختار المستخدم تعديل الكلية أو القسم أو التخصص
                                                ->required(
                                                    fn (Get $get) => in_array('faculty_code', $get('academic_fields') ?? []) ||
                                                        in_array('department_code', $get('academic_fields') ?? []) ||
                                                        in_array('special_code', $get('academic_fields') ?? [])
                                                )
                                                ->afterStateUpdated(function (Set $set) {
                                                    // تفريغ التابع له عند تغيير الكلية
                                                    $set('department_code', null);
                                                    $set('special_code', null);
                                                })
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.faculty_code'))
                                                ->visible(
                                                    fn (Get $get) => in_array('faculty_code', $get('academic_fields') ?? []) ||
                                                        in_array('department_code', $get('academic_fields') ?? []) ||
                                                        in_array('special_code', $get('academic_fields') ?? [])
                                                ),

                                            // ------------------ 2. القسم ------------------
                                            Select::make('department_code')
                                                ->label(__('reg.department'))
                                                ->options(function (Get $get): Collection {
                                                    $facultyCode = $get('faculty_code');
                                                    if (blank($facultyCode)) {
                                                        return collect();
                                                    }

                                                    return Department::forAuthUser()
                                                        ->where('faculty_code', $facultyCode)
                                                        ->pluck('name_ar', 'code');
                                                })
                                                ->live()
                                                ->searchable()
                                                // لا يمكن استخدامه إلا إذا تم اختيار الكلية أولاً
                                                ->disabled(
                                                    fn (Get $get) => blank($get('faculty_code')) ||
                                                        ! auth()->user()?->hasFieldPermission('students.department_code')
                                                )
                                                // إلزامي إذا اختار تعديل القسم أو التخصص
                                                ->required(
                                                    fn (Get $get) => in_array('department_code', $get('academic_fields') ?? []) ||
                                                        in_array('special_code', $get('academic_fields') ?? [])
                                                )
                                                ->afterStateUpdated(function (Set $set) {
                                                    $set('special_code', null);
                                                })
                                                ->visible(
                                                    fn (Get $get) => in_array('department_code', $get('academic_fields') ?? []) ||
                                                        in_array('special_code', $get('academic_fields') ?? [])
                                                ),

                                            // ------------------ 3. التخصص ------------------
                                            Select::make('special_code')
                                                ->label(__('reg.special'))
                                                ->options(function (Get $get): Collection {
                                                    $departmentCode = $get('department_code');
                                                    if (blank($departmentCode)) {
                                                        return collect();
                                                    }

                                                    return Special::query()
                                                        ->where('department_code', $departmentCode)
                                                        ->pluck('name_ar', 'code');
                                                })
                                                ->searchable()
                                                // لا يمكن اختياره إلا بعد تحديد القسم
                                                ->disabled(
                                                    fn (Get $get) => blank($get('department_code')) ||
                                                        ! auth()->user()?->hasFieldPermission('students.special_code')
                                                )
                                                ->required(fn (Get $get) => in_array('special_code', $get('academic_fields') ?? []))
                                                ->visible(fn (Get $get) => in_array('special_code', $get('academic_fields') ?? [])),

                                            Select::make('level_code')
                                                ->label(__('reg.level'))
                                                ->options(fn () => Level::forAuthUser()->pluck('name_ar', 'code'))
                                                ->searchable()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.level_code'))
                                                ->visible(fn (Get $get) => in_array('level_code', $get('academic_fields') ?? [])),

                                            Select::make('stady_code')
                                                ->label(__('reg.stady'))
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::Stady))
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.stady_code'))
                                                ->visible(fn (Get $get) => in_array('stady_code', $get('academic_fields') ?? [])),

                                            Select::make('batch_code')
                                                ->label(__('reg.batch'))
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::AcademicYear))
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.batch_code'))
                                                ->visible(fn (Get $get) => in_array('batch_code', $get('academic_fields') ?? [])),

                                            Select::make('academic_year_code')
                                                ->label(__('reg.academic_year'))
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::AcademicYear))
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.academic_year_code'))
                                                ->visible(fn (Get $get) => in_array('academic_year_code', $get('academic_fields') ?? [])),

                                            Select::make('status_code')
                                                ->label(__('reg.status_code'))
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::Status))
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.status_code'))
                                                ->visible(fn (Get $get) => in_array('status_code', $get('academic_fields') ?? [])),

                                            Select::make('nationality_code')
                                                ->label(__('reg.nationality'))
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::Nationality))
                                                ->searchable()
                                                ->visible(fn (Get $get) => in_array('nationality_code', $get('academic_fields') ?? [])),

                                            Select::make('admission_code')
                                                ->label(__('reg.admission'))
                                                ->options(function () {
                                                    $parents = LtColumn::query()->where('grouped', LtGroup::Admission->value)->whereNull('parent_code')->get();
                                                    $options = [];
                                                    foreach ($parents as $parent) {
                                                        $children = LtColumn::query()->where('grouped', LtGroup::Admission->value)->where('parent_code', $parent->code)->pluck('name_ar', 'code')->toArray();
                                                        if (count($children) > 0) {
                                                            $options[$parent->name_ar] = $children;
                                                        } else {
                                                            $options[$parent->code] = $parent->name_ar;
                                                        }
                                                    }

                                                    return $options;
                                                })
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.admission_code'))
                                                ->visible(fn (Get $get) => in_array('admission_code', $get('academic_fields') ?? [])),

                                            Radio::make('gender_code')
                                                ->label(__('reg.gender'))
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::Gender))
                                                ->inline()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.gender_code'))
                                                ->visible(fn (Get $get) => in_array('gender_code', $get('academic_fields') ?? [])),

                                            Select::make('student_account')
                                                ->label(__('reg.student_account'))
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::StudentAccount))
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.student_account'))
                                                ->visible(fn (Get $get) => in_array('student_account', $get('academic_fields') ?? [])),

                                            Select::make('reg_access')
                                                ->label('حالة التسجيل للطالب')
                                                ->options(\App\Reg\RegAccess::class)
                                                ->placeholder('حسب فترة open-regs')
                                                ->native(false)
                                                ->visible(fn (Get $get) => in_array('reg_access', $get('academic_fields') ?? [])),
                                        ]),
                                ]),

                            // ==========================================
                            // التبويب الثاني: الرسوم المالية
                            // ==========================================
                            Tabs\Tab::make(__('reg.fees_students'))
                                ->icon('heroicon-o-banknotes')
                                ->schema([

                                    CheckboxList::make('fees_fields')
                                        ->label(__('students.bulk_fees_fields'))
                                        ->options([
                                            'reg_fees' => __('reg.regfees'),
                                            'fees' => __('reg.fees'),
                                            'fees1' => __('reg.fees1'),
                                            'fees2' => __('reg.fees2'),
                                            'fees3' => __('reg.fees3'),
                                            'fees5' => __('reg.fees5'),
                                            'fees6' => __('reg.fees6'),
                                            'fees7' => __('reg.fees7'),
                                            'fees8' => __('reg.fees8'),
                                            'fees9' => __('reg.fees9'),
                                            'discount_percent' => __('reg.discount_percent'),
                                            'currency' => __('reg.currency'),
                                            'installment_code' => __('reg.Installments'),
                                        ])
                                        ->columns(4)
                                        ->live(),

                                    Grid::make(4)
                                        ->schema([
                                            TextInput::make('reg_fees')->label(__('reg.regfees'))->numeric()->prefix('$')
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.reg_fees'))
                                                ->visible(fn (Get $get) => in_array('reg_fees', $get('fees_fields') ?? [])),

                                            TextInput::make('fees')->label(__('reg.fees'))->numeric()->prefix('$')
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees'))
                                                ->visible(fn (Get $get) => in_array('fees', $get('fees_fields') ?? [])),

                                            TextInput::make('fees1')->label(__('reg.fees1'))->numeric()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees1'))
                                                ->visible(fn (Get $get) => in_array('fees1', $get('fees_fields') ?? [])),

                                            TextInput::make('fees2')->label(__('reg.fees2'))->numeric()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees2'))
                                                ->visible(fn (Get $get) => in_array('fees2', $get('fees_fields') ?? [])),

                                            TextInput::make('fees3')->label(__('reg.fees3'))->numeric()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees3'))
                                                ->visible(fn (Get $get) => in_array('fees3', $get('fees_fields') ?? [])),

                                            TextInput::make('fees5')->label(__('reg.fees5'))->numeric()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees5'))
                                                ->visible(fn (Get $get) => in_array('fees5', $get('fees_fields') ?? [])),

                                            TextInput::make('fees6')->label(__('reg.fees6'))->numeric()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees6'))
                                                ->visible(fn (Get $get) => in_array('fees6', $get('fees_fields') ?? [])),

                                            TextInput::make('fees7')->label(__('reg.fees7'))->numeric()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees7'))
                                                ->visible(fn (Get $get) => in_array('fees7', $get('fees_fields') ?? [])),

                                            TextInput::make('fees8')->label(__('reg.fees8'))->numeric()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.fees8'))
                                                ->visible(fn (Get $get) => in_array('fees8', $get('fees_fields') ?? [])),

                                            TextInput::make('fees9')->label(__('reg.fees9'))->numeric()
                                                ->disabled(fn (Get $get) => $get('installment_code') != '20050007')
                                                ->visible(fn (Get $get) => in_array('fees9', $get('fees_fields') ?? [])),
                                        ]),

                                    Grid::make(3)
                                        ->schema([
                                            Select::make('discount_percent')
                                                ->label(__('reg.discount_percent'))
                                                ->native()
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::DiscountPercent))
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.discount_percent'))
                                                ->visible(fn (Get $get) => in_array('discount_percent', $get('fees_fields') ?? [])),

                                            ToggleButtons::make('currency')
                                                ->label(__('reg.currency'))
                                                ->options(['SDG' => 'SDG', 'USD' => 'USD', 'SAR' => 'SAR'])
                                                ->colors(['SDG' => 'info', 'USD' => 'warning', 'SAR' => 'success'])
                                                ->icons(['SDG' => 'heroicon-o-pencil', 'USD' => 'heroicon-o-clock', 'SAR' => 'heroicon-o-check-circle'])
                                                ->inline()
                                                ->disabled(fn () => ! auth()->user()?->hasFieldPermission('students.currency'))
                                                ->visible(fn (Get $get) => in_array('currency', $get('fees_fields') ?? [])),

                                            Select::make('installment_code')
                                                ->label(__('reg.Installments'))
                                                ->native(false)
                                                ->options(fn () => LtColumn::optionsFor(LtGroup::Installment))
                                                ->live()
                                                ->visible(fn (Get $get) => in_array('installment_code', $get('fees_fields') ?? [])),
                                        ]),
                                ]),
                        ])->columnSpanFull(),
                ]),
        ];
    }
}
