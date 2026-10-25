<?php

namespace App\Http\Controllers\Res;

use App\Filament\Clusters\Res\Pages\DashboardResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Res\ResultSummaryRequest;
use App\Models\Reg\LtColumn;
use App\Services\Res\AcademicYearService;
use App\Services\Res\MarkEntryService;
use App\Services\Res\ResultSheetService;
use App\Services\Res\YearResultService;
use Illuminate\Contracts\View\View;

/**
 * ملخص نتيجة الفصل: إحصائية المواد والقرارات لمجموعة (كلية/قسم/تخصص اختياري/مستوى/دفعة).
 * الاختيارات في رابط الصفحة فيمكن تحديثها أو مشاركتها، وتُفحص صلاحية الكلية والقسم في الخادم.
 */
class ReportController extends Controller
{
    public function semesterSummary(ResultSummaryRequest $request, ResultSheetService $sheets, MarkEntryService $access): View
    {
        $params = $request->validated();

        abort_unless($access->canAccess($request->user(), $params['faculty'], $params['department'], $params['level']), 403);

        $report = $sheets->build($params + ['absent' => 1], $request->user());

        $subjectStats = collect($report['subjectPassStats'])
            ->map(fn (array $row): array => [
                'subject_name' => $row['name'],
                'total' => $row['total'],
                'passed' => $row['passed'],
                'failed' => $row['failed'],
                'pass_rate' => $row['rate'],
                'grades' => collect($report['subjectStats'][$row['code']] ?? []),
            ])
            ->sortByDesc('pass_rate');

        $recomStats = collect($report['recomStats']);

        return view('Res.result_summary', [
            'totalStudents' => $report['stats']['registered'],
            'stats' => $report['stats'],
            'highestGpa' => $report['highestGpa'],
            'subjectStats' => $subjectStats,
            'recomStats' => $recomStats,
            'totalRecoms' => $recomStats->sum('count'),
            'filters' => [
                'batch' => LtColumn::where('grouped', 2003)->where('code', $params['batch'])->value('name_ar') ?? $params['batch'],
                'faculty' => $report['facultyInfo']?->name_ar ?? 'الكل',
                'department' => $report['departmentInfo']?->name_ar ?? 'الكل',
                'special' => $report['specialInfo']?->name_ar ?? 'الكل',
                'level' => $report['levelInfo']?->name_ar ?? 'الكل',
                'absent' => $report['absentName'],
            ],
            'backUrl' => DashboardResult::getUrl(parameters: collect($params)->filter(fn ($value): bool => filled($value))->all(), panel: 'admin'),
            'printedBy' => $request->user()->name,
        ]);
    }

    /**
     * النتيجة النهائية السنوية (نظام فصلي-سنوي): نتيجة كل فصل ونتيجة السنة من مجموع الفصلين.
     * تُفحص صلاحية الكلية والقسم لكل مستوى من مستويات السنة.
     */
    public function yearResult(ResultSummaryRequest $request, YearResultService $yearResult, AcademicYearService $years, MarkEntryService $access): View
    {
        $params = $request->validated();

        foreach ($years->levelsOfYear($params['level']) as $level) {
            abort_unless($access->canAccess($request->user(), $params['faculty'], $params['department'], $level), 403);
        }

        $report = $yearResult->build($params, $request->user());

        return view('Res.year_result', $report + [
            'backUrl' => DashboardResult::getUrl(parameters: collect($params)->filter(fn ($value): bool => filled($value))->all(), panel: 'admin'),
            'printedBy' => $request->user()->name,
            'batchName' => LtColumn::where('grouped', 2003)->where('code', $params['batch'])->value('name_ar') ?? $params['batch'],
        ]);
    }
}
