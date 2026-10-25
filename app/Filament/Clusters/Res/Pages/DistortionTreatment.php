<?php

namespace App\Filament\Clusters\Res\Pages;

use App\Filament\Clusters\Res\ResCluster;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Models\Res\Grade;
use App\Models\Res\MarkCurve;
use App\Models\Res\Subject;
use App\Reg\AbsStatus;
use App\Services\Res\MarkCurveService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

/**
 * معالجة تشوّهات درجات مادة: رسوم التوزيع والتقديرات، ثم معالجة بنطاق درجات أو بالانحراف المعياري، مع سجل وتراجع.
 * الصفحة للعرض فقط: كل المنطق والصلاحيات في App\Services\Res\MarkCurveService.
 */
class DistortionTreatment extends Page implements HasActions, HasForms
{
    use HasPageShield;
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.clusters.res.pages.distortion-treatment';

    protected static ?string $cluster = ResCluster::class;

    protected static ?string $title = 'distortion_treatment';

    /** @var array<string, mixed>|null */
    public ?array $filters = [
        'batch' => null,
        'faculty' => null,
        'department' => null,
        'level' => null,
        'absent' => AbsStatus::First->value,
        'subject' => null,
    ];

    /** @var array<string, mixed>|null */
    public ?array $curve = [
        'method' => MarkCurve::METHOD_RANGE,
        'range' => [0, 49],
        'delta' => 5,
        'targetMean' => null,
        'targetSd' => null,
        'note' => null,
    ];

    public static function getNavigationLabel(): string
    {
        return __('res.'.static::$title);
    }

    public function getTitle(): string|Htmlable
    {
        return __('res.'.static::$title);
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->statePath('filters')->columns(['sm' => 3])->components([
            Select::make('batch')
                ->label(__('reg.batch'))
                ->options(fn (): Collection => LtColumn::query()->where('grouped', 2003)->orderByDesc('code')->pluck('name_ar', 'code'))
                ->searchable()
                ->live()
                ->required(),
            Select::make('faculty')
                ->label(__('reg.faculty'))
                ->options(fn (): array => Faculty::forAuthUser()->orderBy('name_ar')->pluck('name_ar', 'code')->all())
                ->searchable()
                ->native(false)
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('department', null);
                    $set('subject', null);
                })
                ->required(),
            Select::make('department')
                ->label(__('reg.department'))
                ->options(fn (Get $get): array => blank($get('faculty'))
                    ? []
                    : Department::forAuthUser()->where('faculty_code', $get('faculty'))->orderBy('name_ar')->pluck('name_ar', 'code')->all())
                ->searchable()
                ->native(false)
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('subject', null)),
            Select::make('level')
                ->label(__('reg.level'))
                ->options(fn (): array => Level::forAuthUser()->orderBy('code')->pluck('name_ar', 'code')->all())
                ->searchable()
                ->native(false)
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('subject', null))
                ->required(),
            Select::make('absent')
                ->label(__('res.exam_type'))
                ->options(AbsStatus::class)
                ->default(AbsStatus::First->value)
                ->live()
                ->required(),
            Select::make('subject')
                ->label(__('res.subject'))
                ->options(function (Get $get): array {
                    if (blank($get('faculty')) || blank($get('level'))) {
                        return [];
                    }

                    return Subject::query()
                        ->where('faculty_code', $get('faculty'))
                        ->where('level_code', $get('level'))
                        ->when($get('department'), fn ($query, $department) => $query->where('department_code', $department))
                        ->orderBy('code')
                        ->pluck('name_ar', 'code')
                        ->all();
                })
                ->searchable()
                ->native(false)
                ->live()
                ->required(),
        ]);
    }

    public function curveForm(Schema $schema): Schema
    {
        return $schema->statePath('curve')->columns(['sm' => 2])->components([
            Select::make('method')
                ->label(__('res.distortion_method'))
                ->options([
                    MarkCurve::METHOD_RANGE => __('res.distortion_method_range'),
                    MarkCurve::METHOD_STDDEV => __('res.distortion_method_stddev'),
                ])
                ->live()
                ->required()
                ->columnSpanFull(),
            Section::make(__('res.distortion_method_range'))
                ->visible(fn (Get $get): bool => $get('method') === MarkCurve::METHOD_RANGE)
                ->columns(['sm' => 2])
                ->columnSpanFull()
                ->schema([
                    Slider::make('range')
                        ->label(__('res.distortion_range'))
                        ->range(0, 100)
                        ->step(1)
                        ->minDifference(0)
                        ->tooltips()
                        ->fillTrack([false, true, false])
                        ->live(debounce: 400)
                        ->columnSpanFull(),
                    TextInput::make('delta')
                        ->label(__('res.distortion_delta'))
                        ->helperText(__('res.distortion_delta_help'))
                        ->numeric()
                        ->integer()
                        ->minValue(-100)
                        ->maxValue(100)
                        ->live(onBlur: true),
                ]),
            Section::make(__('res.distortion_method_stddev'))
                ->visible(fn (Get $get): bool => $get('method') === MarkCurve::METHOD_STDDEV)
                ->columns(['sm' => 2])
                ->columnSpanFull()
                ->schema([
                    TextInput::make('targetMean')
                        ->label(__('res.distortion_target_mean'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->live(onBlur: true),
                    TextInput::make('targetSd')
                        ->label(__('res.distortion_target_sd'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(50)
                        ->live(onBlur: true),
                ]),
            TextInput::make('note')
                ->label(__('res.distortion_note'))
                ->maxLength(250)
                ->columnSpanFull(),
        ]);
    }

    public function applyAction(): Action
    {
        return Action::make('apply')
            ->label(__('res.distortion_apply'))
            ->icon('heroicon-m-adjustments-horizontal')
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription(__('res.distortion_apply_confirm'))
            ->action(function (): void {
                $this->guard(function (): void {
                    $subject = $this->subject();

                    if (! $subject) {
                        throw new DomainException(__('res.distortion_pick_subject'));
                    }

                    $curve = app(MarkCurveService::class)->apply(
                        auth()->user(),
                        $subject,
                        $this->filters['batch'],
                        $this->filters['absent'],
                        (string) $this->curve['method'],
                        $this->params(),
                        $this->curve['note'] ?? null,
                    );

                    Notification::make()->success()
                        ->title(__('res.distortion_applied', ['count' => $curve->affected_count]))
                        ->send();
                });
            });
    }

    public function revertAction(): Action
    {
        return Action::make('revert')
            ->label(__('res.distortion_revert'))
            ->icon('heroicon-m-arrow-uturn-left')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(__('res.distortion_revert_confirm'))
            ->action(function (array $arguments): void {
                $this->guard(function () use ($arguments): void {
                    $curve = $this->historyQuery()->findOrFail($arguments['curve']);

                    app(MarkCurveService::class)->revert(auth()->user(), $curve);

                    Notification::make()->success()->title(__('res.distortion_reverted'))->send();
                });
            });
    }

    /**
     * التوزيع الحالي والمتوقع بعد المعالجة، لرسوم الصفحة وجدول التقديرات.
     *
     * @return array{before: array<string, mixed>, after: array<string, mixed>|null, previewError: string|null, grades: Collection<int, Grade>}|null
     */
    public function getStatistics(): ?array
    {
        $subject = $this->subject();

        if (! $subject || blank($this->filters['batch'] ?? null) || blank($this->filters['absent'] ?? null)) {
            return null;
        }

        $service = app(MarkCurveService::class);
        $marks = $service->marksFor($subject, $this->filters['batch'], $this->filters['absent']);
        $plan = (string) ($marks->first()?->student?->stady_code ?: \App\Services\Res\RegulationProfile::current()->defaultPlan());

        $before = $service->distribution($marks->map(fn ($m): int => (int) $m->mark + (int) $m->cwork), $plan);
        $after = null;
        $error = null;

        try {
            $after = $service->distribution(
                $service->projectedTotals($subject, $this->filters['batch'], $this->filters['absent'], (string) $this->curve['method'], $this->params()),
                $plan,
            );
        } catch (DomainException $e) {
            $error = $e->getMessage();
        }

        return [
            'before' => $before,
            'after' => $after,
            'previewError' => $error,
            'grades' => Grade::forPlan($plan)->sortBy('lower')->values(),
        ];
    }

    /**
     * سجل معالجات المادة المختارة (الأحدث أولاً).
     *
     * @return Collection<int, MarkCurve>
     */
    public function getHistory(): Collection
    {
        if (! $this->subject()) {
            return collect();
        }

        return $this->historyQuery()->with(['user', 'revertedBy'])->latest('id')->limit(20)->get();
    }

    /**
     * المادة المختارة بعد التحقق من نطاق صلاحيات المستخدم، لأن حالة الفلاتر قادمة من المتصفح.
     */
    private function subject(): ?Subject
    {
        $code = $this->filters['subject'] ?? null;

        if (blank($code)) {
            return null;
        }

        $subject = Subject::query()->find($code);

        return $subject && auth()->user()->canAccessAcademic($subject->faculty_code, $subject->department_code, $subject->level_code)
            ? $subject
            : null;
    }

    private function historyQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return MarkCurve::query()
            ->where('subject_code', $this->subject()?->code)
            ->where('batch_code', $this->filters['batch'] ?? null)
            ->where('exam_type', $this->filters['absent'] ?? 1);
    }

    /**
     * @return array<string, int|float>
     */
    private function params(): array
    {
        if (($this->curve['method'] ?? null) === MarkCurve::METHOD_STDDEV) {
            return array_filter([
                'targetMean' => $this->curve['targetMean'] ?? null,
                'targetSd' => $this->curve['targetSd'] ?? null,
            ], fn ($value): bool => is_numeric($value));
        }

        [$from, $to] = array_pad((array) ($this->curve['range'] ?? [0, 100]), 2, 100);

        return ['from' => (int) $from, 'to' => (int) $to, 'delta' => (int) ($this->curve['delta'] ?? 0)];
    }

    private function guard(callable $callback): void
    {
        try {
            $callback();
        } catch (DomainException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }
    }
}
