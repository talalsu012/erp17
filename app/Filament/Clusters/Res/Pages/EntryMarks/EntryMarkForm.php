<?php

namespace App\Filament\Clusters\Res\Pages\EntryMarks;

use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Models\Res\Subject;
use App\Reg\AbsStatus;
use App\Reg\Semester;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Collection;

trait EntryMarkForm
{
    public function EntryMarksForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make([

                    Section::make()
                        ->columns(['sm' => 4])
                        ->schema([
                            Select::make('batch')
                                ->label(__('reg.batch'))
                                ->required()
                                ->validationMessages([
                                    'required' => 'عذراً، يجب عليك اختيار الدفعة أولاً لإكمال العملية.',
                                ])
                                ->options(LtColumn::where('grouped', 2003)
                                    ->where('code', '>=', 2023)
                                    ->pluck('name_ar', 'code')

                                )
                                ->searchable()
                                ->live()
                            // ->afterStateUpdated(fn () => $this->table->resetTable())
                            ,

                            Select::make('faculty')
                                ->label(__('reg.faculty'))
                                ->required() // مطلوب إذا تم اختيار الدفعة
                                ->validationMessages([
                                    'required' => 'عذراً، يجب عليك اختيار الكلية أولاً لإكمال العملية.',
                                ])
                                ->options(
                                    Faculty::query()
                                        ->selectRaw('code, CONCAT(name_ar, " (", code, ")") as name_with_code')
                                        ->whereIn('code', auth()->user()->faculties->pluck('code'))
                                        ->pluck('name_with_code', 'code')
                                )
                                ->searchable()
                                ->live()
                                ->columnSpan(2)
                            // ->afterStateUpdated(fn () => $this->table->reload())
                            ,
                            Select::make('department')
                                ->label(__('reg.department'))
                                ->required()
                                ->options(
                                    fn (Get $get): Collection => Department::query()
                                        ->where('faculty_code', $get('faculty'))
                                        ->pluck('name_ar', 'code')
                                )
                                ->searchable()
                                ->dehydrated()
                                ->live(),

                            Select::make('special')
                                ->label(__('reg.special'))
                                ->options(
                                    fn (Get $get): Collection => Special::query()
                                        ->where('department_code', $get('department'))
                                        ->pluck('name_ar', 'code')
                                )
                                ->searchable()
                                ->live()
                                ->dehydrated(),

                            Select::make('level')
                                ->label(__('reg.level'))
                                ->options(Level::all()->pluck('name_ar', 'code'))
                                ->required()

                                ->live()
                            // ->afterStateUpdated(fn () => $this->table->reload())
                            ,

                            // Select::make('semester')
                            //     ->label(__('res.semester'))
                            //     ->options(function () {
                            //         return collect(Semester::cases())
                            //             ->filter(fn($case) => $case->value <= 2)
                            //             ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]);
                            //     })
                            //     ->live()
                            //     ->required()
                            // //    ->afterStateUpdated(fn () => $this->table->reload())
                            // ,

                            Hidden::make('semester')->default(1),

                            Select::make('absent')
                                ->label(__('res.absent'))
                                ->options(AbsStatus::class)
                                ->live()
                                ->required()
                            // ->afterStateUpdated(fn () => $this->table->reload())
                            ,

                            Select::make('subject')
                                ->required()
                                ->label(__('res.subject'))
                                ->options(function (Get $get) {
                                    if (empty($get('faculty')) || empty($get('level')) || empty($get('batch'))) {
                                        return [];
                                    }

                                    $query = Subject::query()
                                        ->Exam()
                                        ->where('faculty_code', $get('faculty'))
                                        ->when($get('department'), fn ($query) => $query->where('department_code', $get('department')))
                                        ->when($get('special'), fn ($query) => $query->where('special_code', $get('special')))
                                        ->where('level_code', $get('level'));

                                    // ->where('semester_id', $get('semester'))
                                    return $query->get()->pluck('name_ar', 'code');
                                })
                                ->columnSpan(2)
                                ->live() // ضروري جداً لكي يعمل التحديث اللحظي
                                ->afterStateUpdated(fn () => $this->StudentEntryFn()),

                            Actions::make([
                                Action::make('ShowStudents')
                                    ->label(__('reg.students'))
                                    ->icon('heroicon-m-pencil-square')
                                    ->size(Size::Large)
                                    ->color(Color::Blue)
                                    ->action('StudentEntryFn'),
                            ])
                                ->visible()
                                ->verticalAlignment(VerticalAlignment::End),

                            Actions::make([
                                Action::make('AproveMarks')
                                    ->label(__('res.approve_marks'))
                                    ->icon('heroicon-m-pencil-square')
                                    ->size(Size::Large)
                                    ->color(Color::Amber)
                                    ->visible(function (Get $get) {
                                        $subject = $get('subject');
                                        $batch = $get('batch');
                                        if (! $subject || ! $batch) {
                                            return false;
                                        }
                                        $currentStage = $this->getResultService()->getCurrentStage($subject, $batch);

                                        return auth()->user()->hasRole($currentStage);
                                    })
                                    ->action(
                                        function (Get $get) {
                                            $subjectCode = $get('subject');
                                            $batchCode = $get('batch');
                                            $this->getResultService()->advanceStage($subjectCode, $batchCode);

                                        }
                                    ),
                            ])
                                ->visible()
                                ->verticalAlignment(VerticalAlignment::End),

                            // Callout::make('StudentCount')
                            //     ->description(fn() => $this->StudentCount)
                            //     ->info(),
                        ]),

                ])->from('md'),

            ]);
    }

    public function StudentEntryFn()
    {
        $this->validate(); // تأكد من أن جميع الحقول المطلوبة تم ملؤها بشكل صحيح

        try {
            // تنفيذ التحقق (Validation) لجميع الحقول في الفورم

            // إذا كان هناك حقل ناقص، سيتوقف الكود هنا ويرمي ValidationException تلقائياً
            $data = $this->form->getState();
            // تحديث متغير المادة في الكلاس لبدء فلترة الجدول
            $this->subjectCode = $data['subject'] ?? null;
            // إعادة تحميل بيانات الجدول بناءً على المادة الجديدة
            $this->resetTable();

            // اختياري: إرسال إشعار بسيط بنجاح العملية
            Notification::make()
                ->title('تم تحميل قائمة الطلاب')
                ->success()
                ->send();

        } catch (\Illuminate\Validation\ValidationException) {
            // في حال فشل التحقق، نقوم بتفريغ الـ subjectCode لكي لا يظهر جدول خاطئ
            $this->subjectCode = null;

            // Filament سيتكفل بإظهار رسائل الخطأ تحت الحقول تلقائياً
            // اختياري: إرسال إشعار بفشل العملية
            Notification::make()
                ->title('خطأ في الإدخال')
                ->body('يرجى التأكد من ملء جميع الحقول المطلوبة بشكل صحيح.')
                ->danger()
                ->send();
        }
    }
}
