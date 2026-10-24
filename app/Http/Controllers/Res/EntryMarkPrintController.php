<?php

namespace App\Http\Controllers\Res;

use App\Filament\Clusters\Res\Pages\EntryMarks\EntryMark;
use App\Http\Controllers\Controller;
use App\Http\Requests\Res\EntryMarkPrintRequest;
use App\Models\Reg\LtColumn;
use App\Models\Res\Subject;
use App\Reg\AbsStatus;
use App\Services\Res\MarkEntryService;
use App\Services\Res\ResultSheetService;
use Illuminate\Contracts\View\View;

/**
 * كشف طباعة مادة بطلابها ودرجاتهم وتقديراتهم ونقاطهم.
 * يستعمل نفس استعلام شاشة الإدخال فالمطبوع يطابق المعروض، ونفس ترويسة وتصميمات كشف النتيجة.
 * بلا عنوان علوي ولا نص سفلي (هما لكشف النتيجة الرسمي)، والتوقيع الوحيد للمستخدم المُدخِل.
 * الرابط قابل للتعديل من المتصفح، لذلك تُفحص صلاحية الكلية في الخادم.
 */
class EntryMarkPrintController extends Controller
{
    public function __invoke(EntryMarkPrintRequest $request, MarkEntryService $service, ResultSheetService $sheets): View
    {
        $filters = $request->validated() + ['semester' => 1];
        $user = $request->user();

        $subject = Subject::query()
            ->with(['faculty', 'department', 'special', 'level'])
            ->findOrFail($filters['subject']);

        abort_unless($service->canAccess($user, $filters['faculty'], $filters['department'] ?? null, $filters['level']), 403);
        abort_unless((string) $subject->faculty_code === (string) $filters['faculty'], 403);

        $rows = $service->studentsQuery($user, $filters)->orderBy('name_ar')->get()->map(function ($student): array {
            $mark = $student->marks->first();
            $entered = $mark && ($mark->mark !== null || $mark->cwork !== null);

            return [
                'university_no' => $student->university_no,
                'name' => $student->name_ar,
                'mark' => $mark?->mark,
                'cwork' => $mark?->cwork,
                'total' => $entered ? (int) $mark->mark + (int) $mark->cwork : null,
                'grade' => $mark?->grade,
                'point' => $mark?->point,
            ];
        });

        return view('Res.sheets.subject', [
            'subject' => $subject,
            'rows' => $rows,
            'showCwork' => ($subject->exam ?? 100) < 100,
            'design' => $filters['design'] ?? 'page_header',
            'designs' => DashBoardResultController::DESIGNS,
            'backUrl' => EntryMark::urlWithFilters($filters),
            'perPage' => $sheets->perPage($filters['per_page'] ?? null),
            'logos' => $sheets->logos($subject->faculty_code),
            'examType' => AbsStatus::from((int) $filters['absent'])->getLabel(),
            'batchName' => LtColumn::query()->where('grouped', 2003)->where('code', $filters['batch'])->value('name_ar'),
            'stage' => $service->currentStage($subject->code, $filters['batch'])->getLabel(),
            'entered' => $rows->filter(fn (array $row): bool => $row['grade'] !== null)->count(),
            'printedBy' => $user->name,
            'signatories' => $sheets->withNames([
                ['name' => $user->name, 'title' => 'مُدخل الدرجات'],
            ]),
        ]);
    }
}
