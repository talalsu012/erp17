<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatusWidget extends StatsOverviewWidget
{
    protected static ?string $title = 'Finance dashboard';

    protected function getStats(): array
    {

        return [
            Stat::make(__('reg.Complete'), $this->getStatus('20070001'))
                ->description($this->getStatusDesc('20070001'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make(__('reg.noreg'), $this->getStatus('20070002'))
                ->description($this->getStatusDesc('20070002'))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),
            Stat::make(__('reg.unRegister'), $this->getStatus('20070003'))
                ->description($this->getStatusDesc('20070003'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
            Stat::make(__('reg.students'), $this->getTotalStudents())
                // ->description('32k increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),
        ];
    }

    public function getTotalStudents()
    {
        return Student::whereHas('faculty', function ($q) {
            $q->whereHas('users', function ($userQuery) {
                $userQuery->where('user_id', auth()->id());
            });
        })->count();
    }

    public function getStatus($val = 0)
    {
        return Student::whereHas('faculty', function ($q) {
            $q->whereHas('users', function ($userQuery) {
                $userQuery->where('user_id', auth()->id());
            });
        })
            ->where('xstatus', $val)
            ->count();
    }

    public function getStatusDesc($val = 0)
    {
        $totalStudents = $this->getTotalStudents();

        return Number::format(
            $this->getStatus($val) === 0 ? 0 : $this->getStatus($val) / $totalStudents * 100, precision: 2).'%';
    }
}
