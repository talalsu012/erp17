<?php

namespace App\Filament\Widgets;

use App\Models\Reg\Faculty;
use App\Models\Student;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FeesPaymentChart extends ChartWidget
{
    // ✅ هذه properties عادية وليست static
    protected ?string $heading = 'نسبة دفع الرسوم حسب الكلية';

    protected ?string $description = 'نسبة المدفوع إلى إجمالي الرسوم لكل كلية';

    protected int|string|array $columnSpan = 'full';
    // protected static ?string $maxHeight = '400px';

    // ✅ لا تحتاج override لـ getHeading() unless you want dynamic heading
    // 但如果 تريد عنوان ديناميكي:
    public function getHeading(): string
    {
        return $this->heading.' • '.now()->format('Y');
    }

    protected function getData(): array
    {
        // استخدام Faculty Model
        $faculties = Faculty::ForAuthUser()
            ->whereHas('students', function ($query) {
                $query->where('all_fees', '>', 0);
            })

            ->get();

        $labels = [];
        $percentages = [];
        $studentsCount = [];
        $totalPaid = [];
        $totalFees = [];
        $colors = [];

        $colorPalette = [
            '#3b82f6', // أزرق
            '#10b981', // أخضر
            '#f59e0b', // برتقالي
            '#ef4444', // أحمر
            '#8b5cf6', // بنفسجي
            '#ec4899', // وردي
            '#06b6d4', // سماوي
            '#f97316', // برتقالي غامق
            '#6366f1', // نيلي
            '#14b8a6', // فيروزي
        ];

        foreach ($faculties as $index => $faculty) {
            $stats = Student::where('faculty_code', $faculty->code)
                ->where('all_fees', '>', 0)
                ->select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw('ROUND(AVG((pay / all_fees) * 100), 2) as avg_percentage'),
                    DB::raw('SUM(pay) as paid'),
                    DB::raw('SUM(all_fees) as fees')
                )
                ->first();

            if ($stats && $stats->count > 0) {
                $labels[] = $faculty->name_ar ?? "كلية {$faculty->code}";
                $percentages[] = $stats->avg_percentage ?? 0;
                $studentsCount[] = $stats->count;
                $totalPaid[] = $stats->paid ?? 0;
                $totalFees[] = $stats->fees ?? 0;
                $colors[] = $colorPalette[$index % count($colorPalette)];
            }
        }

        // ترتيب تنازلي حسب النسبة
        array_multisort($percentages, SORT_DESC, $labels, $studentsCount, $totalPaid, $totalFees, $colors);

        return [
            'datasets' => [
                [
                    'label' => 'نسبة الدفع (%)',
                    'data' => $percentages,
                    'backgroundColor' => $colors,
                    'borderRadius' => 6,
                    'barPercentage' => 0.7,
                    'categoryPercentage' => 0.8,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'نسبة الدفع (%)',
                        'font' => [
                            'weight' => 'bold',
                            'size' => 12,
                        ],
                    ],
                    'grid' => [
                        'color' => '#e2e8f0',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'afterBody' => function ($context) {
                            if (isset($context[0])) {
                                $index = $context[0]['dataIndex'];
                                $data = $this->getData();

                                if (isset($data['datasets'][0]['data'][$index])) {
                                    $percentage = $data['datasets'][0]['data'][$index];
                                    $facultyName = $data['labels'][$index];

                                    // جلب إحصائيات إضافية
                                    $faculty = Faculty::where('name_ar', $facultyName)
                                        ->orWhere('name_en', $facultyName)
                                        ->first();

                                    if ($faculty) {
                                        $stats = Student::where('faculty_code', $faculty->code)
                                            ->where('all_fees', '>', 0)
                                            ->select(
                                                DB::raw('COUNT(*) as count'),
                                                DB::raw('SUM(pay) as paid'),
                                                DB::raw('SUM(all_fees) as fees')
                                            )
                                            ->first();

                                        if ($stats) {
                                            return [
                                                '',
                                                'عدد الطلاب: '.number_format($stats->count),
                                                'المدفوع: '.number_format($stats->paid ?? 0, 2).' ر.س',
                                                'المستحق: '.number_format($stats->fees ?? 0, 2).' ر.س',
                                                "نسبة التحصيل: {$percentage}%",
                                            ];
                                        }
                                    }
                                }
                            }

                            return [];
                        },
                    ],
                ],
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
