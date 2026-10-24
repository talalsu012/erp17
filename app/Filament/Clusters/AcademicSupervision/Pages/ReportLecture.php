<?php

namespace App\Filament\Clusters\AcademicSupervision\Pages;

use App\Filament\Clusters\AcademicSupervision\AcademicSupervisionCluster;
use App\Models\attendances\Lecture;
use App\Models\attendances\Staff;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Enums\Width;

class ReportLecture extends Page
{
    use HasPageShield;

    protected string $view = 'filament.clusters.academic-supervision.pages.report-lecture';

    protected static ?string $cluster = AcademicSupervisionCluster::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::ScreenTwoExtraLarge;
    }

    protected static ?string $title = 'تقرير تنفيذ محاضرات الأساتذة';
    // تعريف متغير لحفظ التاريخ المختار (افتراضياً تاريخ اليوم)

    public $date_excutive;

    public $date_excutive_formatted;

    public $staff_id;

    public function AttendanceForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columns([
                        'sm' => 4,
                    ])
                    ->schema([

                        DatePicker::make('date_excutive')
                            ->label(__('Attendance.date_excutive'))
                            ->live(),
                        Select::make('staff_id')
                            ->label(__('Select Staff'))
                            ->options(Staff::query()->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->columnSpan(2),

                        Actions::make([
                            Action::make('LectureDate')
                                ->label(__('Search Schedule'))
                                ->action('LectureDateFunction')
                                ->requiresConfirmation()
                                ->modalHeading(__('Update Lecture Schedule'))
                                ->modalDescription(__('Are you sure you want to update this lecture\'s date and time?'))
                                ->icon('heroicon-m-clock')
                                ->color('info')
                                ->outlined(),
                        ])
                            // ->visible(fn() => !empty($this->lectureID))
                            ->verticalAlignment(VerticalAlignment::End),

                    ])
                    ->grow(false),
            ]);
    }

    public function LectureDateFunction(): void
    {

        $this->date_excutive_formatted = Carbon::parse($this->date_excutive)->format('Y-m-d');
        Notification::make()
            ->title(__('Lecture schedules searched successfully!'))
            ->success()
            ->send();
    }

    /**
     * جلب وتمرير البيانات المفلترة بناءً على اليوم المختار
     */
    protected function getViewData(): array
    {
        // جلب التاريخ المختار من مصفوفة البيانات، أو استخدام تاريخ اليوم كقيمة تراجعية آمنة
        $dateString = $this->date_excutive_formatted ?? '';
        $carbonDate = Carbon::parse($dateString);

        // استخراج اسم اليوم باللغة الإنجليزية بحروف صغيرة ليطابق حقل day_of_week
        $dayName = strtolower($carbonDate->format('l'));

        // الاستعلام الأكاديمي المحمي لمنع استعلامات N+1 وتصفية السجلات حسب اليوم
        $lectures = Lecture::with([
            'subject',
            'staff',
            'room',
            'grouping',
            'lectureStaff',
        ])
            ->where([
                ['start_date', '<=', $dateString],
                ['end_date', '>=', $dateString],
                ['day_of_week', $dayName],
            ])
            ->orWhere('staff_id', $this->staff_id)
            ->get();
        // dd($lectures);
        $daysMapping = [
            'sunday' => 'الأحد',
            'monday' => 'الإثنين',
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            'friday' => 'الجمعة',
            'saturday' => 'السبت',
        ];

        return [
            'lectures' => $lectures,
            'daysMapping' => $daysMapping,
            'formattedDate' => $dateString,
            'dayName' => $dayName,
        ];
    }

    /**
     * حساب فروقات التوقيت ونسبة تنفيذ المحاضرة
     */
    public function getExecutionStatsAttribute(): array
    {
        $stats = [
            'scheduled_minutes' => 0,
            'actual_minutes' => 0,
            'percentage' => 0,
            'diff_minutes' => 0,
        ];

        // 1. حساب المدة المجدولة أصلاً للمحاضرة
        if ($this->start_time && $this->end_time) {
            $stats['scheduled_minutes'] = $this->start_time->diffInMinutes($this->end_time);
        }

        // 2. حساب المدة الحقيقية التي نفذها الأستاذ
        $execution = $this->lectureStaff;
        if ($execution && $execution->is_excutive && $execution->actual_excutive_start_time && $execution->actual_excutive_end_time) {
            $stats['actual_minutes'] = $execution->actual_excutive_start_time->diffInMinutes($execution->actual_excutive_end_time);
        }

        // 3. حساب نسبة التنفيذ والفارق
        if ($stats['scheduled_minutes'] > 0) {
            $stats['percentage'] = round(($stats['actual_minutes'] / $stats['scheduled_minutes']) * 100);
        }

        $stats['diff_minutes'] = $stats['actual_minutes'] - $stats['scheduled_minutes'];

        return $stats;
    }
}
