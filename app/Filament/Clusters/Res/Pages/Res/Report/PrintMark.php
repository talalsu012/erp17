<?php

namespace App\Filament\Clusters\Res\Pages\Res\Report;

use App\Filament\Clusters\Res\ResCluster;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Reg\Special;
use App\Models\Res\Mark;
use App\Models\Res\Printed;
use App\Models\Res\Subject;
use App\Reg\AbsStatus;
use App\Reg\Semester;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class PrintMark extends Page
{
    use HasPageShield;
    // protected string $view = 'filament.clusters.res.pages.res.report.print-mark';

    protected static ?string $cluster = ResCluster::class;

    protected static ?string $model = Mark::class;

    // disapled and not show in system
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('res.print');
    }

    public function getTitle(): string|Htmlable
    {
        return __('res.print');
    }

    public $batch;

    public $faculty;

    public $department;

    public $special;

    public $level;

    public $semester;

    public $absent;

    public $marksDetails = [];

    public $fields = [];

    public $subjects = [];

    public $grades = [];

    public $search_results = null;

    public function EntryMarksForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columns(['sm' => 5])
                    ->schema([
                        Select::make('batch')
                            ->label(__('reg.batch'))
                            ->required()
                            ->validationMessages([
                                'required' => 'عذراً، يجب عليك اختيار العام الدراسي أولاً لإكمال العملية.',
                            ])
                            ->options(LtColumn::where('grouped', 2003)->pluck('name_ar', 'code'))
                            ->searchable()
                            ->live(),

                        Select::make('faculty')
                            ->label(__('reg.faculty'))
                            ->required()
                            ->options(
                                Faculty::query()
                                    ->selectRaw('code, CONCAT(name_ar, " (", code, ")") as name_with_code')
                                    ->pluck('name_with_code', 'code')
                            )
                            ->searchable()
                            ->live()
                            ->native()
                            ->columnSpan(4),

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
                            ->live()
                            ->columnSpan(2),

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
                            ->native()->columnSpan(2),

                        // Select::make('semester')
                        //     ->label(__('res.semester'))
                        //     ->options(Semester::class)
                        //     ->live()
                        //     ->native()
                        //     ->required(),
                        Hidden::make('semester')->default(1),

                        Select::make('absent')
                            ->label(__('res.absent'))
                            ->options(AbsStatus::class)
                            ->live()
                            ->native()
                            ->required(),

                        Select::make('subjects')
                            // ->required()
                            ->label(__('res.subjects'))
                            ->options(function (Get $get) {
                                // تأكد من أن جميع الحقول المطلوبة لها قيمة قبل جلب المقررات
                                if (empty($get('faculty')) || empty($get('level')) || empty($get('batch'))) {
                                    return []; // إرجاع مصفوفة فارغة إذا كانت أي من الحقول فارغة
                                }

                                return Subject::query()
                                    //  ->Exam()
                                    ->where('faculty_code', $get('faculty'))
                                    ->where('level_code', $get('level'))
                                    ->when($get('department'), fn ($query) => $query->where('department_code', $get('department')))
                                    ->when($get('special'), fn ($query) => $query->where('special_code', $get('special')))
                                    ->get()
                                    ->pluck('name_ar', 'code');
                            })
                            ->visible(
                                // حقل Subject هيكون مرئي فقط لو كل الحقول دي ليها قيمة
                                fn (Get $get) => ! empty($get('faculty')) && ! empty($get('level')) && ! empty($get('batch')))
                            ->multiple()
                            ->dehydrated(true) // تأكد من عدم حفظ القيمة إذا كان الحقل مخفيًا
                            ->live() // مهم لتحديث الخيارات عند تغيير الحقول الأخرى
                            ->native(false)
                            ->columnSpan(2),

                        Select::make('grades')
                            ->label(__('res.grade'))
                            ->options(
                                Mark::groupBy('grade')
                                    ->pluck('grade', 'grade')
                            )
                            ->live()
                            ->native()
                            ->multiple()
                            ->required()
                            ->visible(
                                // حقل Subject هيكون مرئي فقط لو كل الحقول دي ليها قيمة
                                fn (Get $get) => ! empty($get('faculty')) && ! empty($get('level')) && ! empty($get('batch')))
                            ->columnSpan(2),

                        CheckboxList::make('fields')
                            ->label(__('res.fields'))
                            ->options(
                                Printed::where('type', '>=', 1)
                                    ->pluck('name_ar', 'fields')
                            )
                            ->default(['1', '2', '7'])
                            ->rules(['required', 'array', 'min:1'])
                            ->validationMessages([
                                'required' => 'يجب اختيار الحقول المطلوبة',
                                'min' => 'يجب اختيار حقل واحد على الأقل',
                            ])
                            ->searchable()
                            ->columns(4)
                            ->columnSpan(5),

                        Actions::make([
                            Action::make('Print-Details')
                                ->label(__('res.print'))
                                ->icon('heroicon-m-plus')
                                ->color('primary')
                                ->action('PrintDetails'),
                        ])
                            ->visible()
                            ->verticalAlignment(VerticalAlignment::End),
                    ]),

            ]);
    }

    /**
     * دالة طباعة الدرجات
     */
    public function PrintDetails()
    {
        $validatedData = $this->validate([
            'faculty' => ['required'],
            'level' => ['required'],
            'batch' => ['required'],
            'semester' => ['required'],
            'absent' => ['required'],
            // 'subjects' => ['required', 'array', 'min:1'],
            'fields' => ['required', 'min:1'],
        ]);

        $dataForm = [
            'faculty' => $this->faculty,
            'department' => $this->department,
            'special' => $this->special,
            'level' => $this->level,
            'batch' => $this->batch,
            'semester' => $this->semester,
            'absent' => $this->absent,
            'subjects' => $this->subjects,
            'fields' => implode(',', $this->fields ?? []),
            'grades' => $this->grades,
        ];

        return redirect()->route('student.results', [
            'dataForm' => base64_encode(json_encode($dataForm)),
        ]);
        // return redirect()->route('print.details', $dataForm);
    }
}
