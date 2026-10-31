<?php

namespace App\Filament\Clusters\Invoices\Pages;

use App\Filament\Clusters\Invoices\InvoicesCluster;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Reg\LtColumn;
use App\Reg\LtGroup;
use App\Services\Reg\BulkStudentFeeService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class BulkStudentFees extends Page
{
    use HasPageShield;

    protected string $view = 'filament.clusters.invoices.pages.bulk-student-fees';

    protected static ?string $cluster = InvoicesCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 5;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var array{count: int, samples: list<array<string, mixed>>}|null */
    public ?array $preview = null;

    public static function getNavigationLabel(): string
    {
        return __('invoice.bulk_fees.title');
    }

    public function getTitle(): string|Htmlable
    {
        return __('invoice.bulk_fees.title');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Select::make('faculty_code')
                    ->label(__('reg.faculty'))
                    ->options(fn () => auth()->user()->constrainToAcademicScope(Faculty::query(), ['faculty' => 'code'])->pluck('name_ar', 'code'))
                    ->searchable()->required()->live()
                    ->afterStateUpdated(fn (callable $set) => $set('department_code', null)),
                Select::make('department_code')
                    ->label(__('reg.department'))
                    ->options(fn (callable $get) => Department::query()->where('faculty_code', $get('faculty_code'))->pluck('name_ar', 'code'))
                    ->searchable()->required(),
                Select::make('level_code')
                    ->label(__('reg.level'))
                    ->options(fn () => Level::query()->pluck('name_ar', 'code'))
                    ->searchable()->required(),
                Select::make('batch_code')
                    ->label(__('reg.batch'))
                    ->options(fn () => LtColumn::query()->where('grouped', '2003')->pluck('name_ar', 'code'))
                    ->searchable()->required(),
                Select::make('fee_type')
                    ->label(__('invoice.bulk_fees.fee_type'))
                    ->options(collect(BulkStudentFeeService::FEE_TYPES)->mapWithKeys(fn (string $field): array => [
                        $field => __($field === 'reg_fees' ? 'reg.regfees' : "reg.{$field}"),
                    ])->all())
                    ->required(),
                TextInput::make('amount')
                    ->label(__('invoice.bulk_fees.amount'))
                    ->numeric()->minValue(0)->required(),
                Select::make('installment_code')
                    ->label(__('reg.Installments'))
                    ->options(fn () => LtColumn::optionsFor(LtGroup::Installment)),
                Actions::make([
                    Action::make('preview')
                        ->label(__('invoice.bulk_fees.preview'))
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->action(fn () => $this->previewFees()),
                    Action::make('apply')
                        ->label(__('invoice.bulk_fees.apply'))
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn (): bool => ($this->preview['count'] ?? 0) > 0)
                        ->requiresConfirmation()
                        ->modalHeading(__('invoice.bulk_fees.confirm_heading'))
                        ->modalDescription(fn (): string => __('invoice.bulk_fees.confirm_description', ['count' => $this->preview['count'] ?? 0]))
                        ->action(fn () => $this->applyFees()),
                ]),
            ]);
    }

    public function previewFees(): void
    {
        $state = $this->form->getState();

        try {
            $this->preview = app(BulkStudentFeeService::class)->preview(auth()->user(), $this->filters($state), $state);
        } catch (DomainException $exception) {
            $this->fail($exception);
        }
    }

    public function applyFees(): void
    {
        $state = $this->form->getState();

        try {
            $changed = app(BulkStudentFeeService::class)->apply(auth()->user(), $this->filters($state), $state);
        } catch (DomainException $exception) {
            $this->fail($exception);

            return;
        }

        $this->preview = null;

        Notification::make()->title(__('invoice.bulk_fees.done', ['count' => $changed]))->success()->send();
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function filters(array $state): array
    {
        return array_intersect_key($state, array_flip(['faculty_code', 'department_code', 'level_code', 'batch_code']));
    }

    private function fail(DomainException $exception): void
    {
        $this->preview = null;

        Notification::make()->title($exception->getMessage())->danger()->send();
    }
}
