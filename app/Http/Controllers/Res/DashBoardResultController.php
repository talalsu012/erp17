<?php

namespace App\Http\Controllers\Res;

use App\Filament\Clusters\Res\Pages\DashboardResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Res\ResultSheetRequest;
use App\Services\Res\MarkEntryService;
use App\Services\Res\ResultSheetService;
use Illuminate\Contracts\View\View;

/**
 * كشف نتيجة الطلاب للطباعة (حالي أو سابق) بأحد التصميمين.
 * البيانات تصل في رابط الصفحة، فيمكن تحديثها أو مشاركتها دون فقدان الاختيارات،
 * والرابط قابل للتعديل من المتصفح لذلك تُفحص صلاحية الكلية في الخادم.
 */
class DashBoardResultController extends Controller
{
    public const DESIGNS = [
        'page_header' => 'ترويسة كاملة في كل صفحة',
        'continuous' => 'جدول متصل مع ملخص التقديرات',
    ];

    /**
     * رابط صفحة اختيار الطباعة وفيه الاختيارات نفسها لإعادة الطباعة بتعديل بسيط.
     *
     * @param  array<string, mixed>  $params
     */
    public static function backUrl(array $params): string
    {
        $form = collect($params)
            ->only(['batch', 'faculty', 'department', 'special', 'level', 'absent', 'subjects', 'fields', 'result_type', 'design', 'show_history'])
            ->put('countstudent', $params['per_page'] ?? null)
            ->filter(fn ($value): bool => filled($value) || $value === 0)
            ->all();

        return DashboardResult::getUrl(parameters: $form, panel: 'admin');
    }

    public function index(ResultSheetRequest $request, ResultSheetService $sheets, MarkEntryService $access): View
    {
        $params = $request->validated();

        abort_unless($access->canAccess($request->user(), $params['faculty'], $params['department'] ?? null, $params['level']), 403);

        return view('Res.sheets.result', $sheets->build($params, $request->user()) + [
            'design' => $params['design'] ?? 'page_header',
            'designs' => self::DESIGNS,
            'backUrl' => self::backUrl($params),
        ]);
    }
}
