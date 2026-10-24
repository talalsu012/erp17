<?php

use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use App\Models\Student;
use Tests\TestCase;

uses(TestCase::class);

const DESIGNS = ['page_header' => 'ترويسة كاملة في كل صفحة', 'continuous' => 'جدول متصل مع ملخص التقديرات'];

function sheetStudents(int $count): Illuminate\Support\Collection
{
    return collect(range(1, $count))->map(function (int $i) {
        $mark = (new Mark)->forceFill(['subject_code' => 'S1', 'mark' => 60 + $i, 'grade' => $i % 5 === 0 ? 'F' : 'B', 'point' => 3.0]);
        $mark->setRelation('subject', (new Subject)->forceFill(['code' => 'S1', 'name_ar' => 'مادة اختبار', 'crch' => 3]));

        $recom = (new Recom)->forceFill(['recom_code' => 1005, 'gpa' => 3.25, 'sumMarks' => 200, 'crch' => 3, 'points' => 9, 'crch_fail' => 0]);
        $recom->setRelation('recomName', null);

        return (new Student)->forceFill(['university_no' => 1000 + $i, 'name_ar' => "طالب رقم {$i}"])
            ->setRelation('marks', collect([$mark]))
            ->setRelation('sheetRecom', $recom);
    });
}

function renderResult(int $students, array $overrides = []): string
{
    $faculty = (object) ['name_ar' => 'كلية الاختبار', 'code' => 10, 'metadata' => []];

    return view('Res.sheets.result', $overrides + [
        'design' => 'page_header', 'designs' => DESIGNS,
        'facultyInfo' => $faculty, 'departmentInfo' => (object) ['name_ar' => 'قسم الاختبار'], 'levelInfo' => (object) ['name_ar' => 'المستوى الأول'],
        'absentName' => 'الاساسي', 'academicYear' => '2026/2027', 'isPrevious' => false,
        'students' => sheetStudents($students), 'subjects' => ['S1'],
        'subjectNames' => collect(['S1' => (object) ['name_ar' => 'مادة اختبار', 'crch' => 3]]),
        'fields' => ['ids', 'university_no', 'name_ar', 'grade', 'mark', 'gpa', 'recom'],
        'perPage' => 16, 'showHistory' => false, 'history' => collect(),
        'counts' => ['external' => collect(), 'repeat' => collect()],
        'stats' => ['registered' => $students, 'sitting' => $students, 'passed' => $students - 4, 'failed' => 4],
        'subjectStats' => ['S1' => ['B' => $students - 4, 'F' => 4]],
        'highestGpa' => 3.5,
        'recomStats' => [['code' => 1001, 'name' => 'ناجح', 'count' => 3, 'percent' => 75.0], ['code' => 2001, 'name' => 'إعادة', 'count' => 1, 'percent' => 25.0]],
        'subjectPassStats' => [['code' => 'S1', 'name' => 'مادة اختبار', 'total' => 4, 'passed' => 3, 'failed' => 1, 'alternate' => 0, 'max' => 90, 'min' => 40, 'avg' => 70.5, 'rate' => 75.0]],
        'logos' => ['university' => null, 'faculty' => null],
        'signatories' => [
            ['name' => 'أ', 'title' => 'رئيس المكتب الأكاديمي'], ['name' => 'ب', 'title' => 'المسجل'],
            ['name' => 'ج', 'title' => 'رئيس القسم'], ['name' => 'د', 'title' => 'عميد الكلية'],
        ],
    ])->render();
}

it('splits students into pages of 16 and pads the last page with empty rows', function () {
    $html = renderResult(20);

    preg_match_all('/<tbody>(.*?)<\/tbody>/s', $html, $bodies);

    expect(substr_count($html, 'data-sheet'))->toBe(2)
        ->and(array_map(fn (string $body) => substr_count($body, '<tr'), array_slice($bodies[1], 0, 2)))->toBe([16, 16]);

    expect($html)->toContain('طالب رقم 1')->toContain('طالب رقم 20')->toContain('صفحة 2 من 2');
});

it('keeps the printed row count equal on every page', function () {
    $html = renderResult(5);

    preg_match('/<tbody>(.*?)<\/tbody>/s', $html, $m);

    expect(substr_count($m[1], '<tr'))->toBe(16);
});

it('switches layout by design', function () {
    $classic = renderResult(3, ['design' => 'page_header']);
    $continuous = renderResult(3, ['design' => 'continuous']);

    expect($classic)->toContain('data-design="page_header"')->toContain('بسم الله الرحمن الرحيم')->not->toContain('المادة \ التقدير');
    expect($continuous)->toContain('data-design="continuous"')->not->toContain('بسم الله الرحمن الرحيم')
        ->toContain('المادة \ التقدير')   // صفحة ملخص التقديرات الأخيرة
        ->toContain('رئيس القسم');         // 4 تواقيع في التصميم المتصل
    expect($classic)->not->toContain('رئيس القسم');
});

it('adds the previous-results columns on request', function () {
    $history = collect([1001 => ['batch' => 2025, 'level' => 'المستوى الأول', 'gpa' => 2.8, 'recom' => 'نجاح']]);

    $html = renderResult(2, ['showHistory' => true, 'history' => $history]);

    expect($html)->toContain('العام السابق')->toContain('المعدل السابق')->toContain('القرار السابق')
        ->toContain('2025 — المستوى الأول')->toContain('2.8');

    expect(renderResult(2))->not->toContain('العام السابق');
});

it('marks failed grades and labels previous sheets', function () {
    $html = renderResult(5, ['isPrevious' => true]);

    expect($html)->toContain('data-fail')->toContain('نتيجة سابقة');
});

it('renders the subject sheet with cwork, grade and points', function () {
    $subject = (new Subject)->forceFill(['code' => 5, 'name_ar' => 'رياضيات', 'crch' => 3, 'exam' => 70]);
    foreach (['faculty', 'department', 'special', 'level'] as $relation) {
        $subject->setRelation($relation, null);
    }

    $html = view('Res.sheets.subject', [
        'subject' => $subject, 'design' => 'page_header', 'designs' => DESIGNS, 'perPage' => 16,
        'rows' => collect([['university_no' => 111, 'name' => 'طالب تجريبي', 'mark' => 60, 'cwork' => 20, 'total' => 80, 'grade' => 'A', 'point' => 4.0]]),
        'showCwork' => true, 'logos' => ['university' => null, 'faculty' => null],
        'examType' => 'الاساسي', 'batchName' => 'دفعة 2026', 'stage' => 'الأستاذ', 'entered' => 1, 'printedBy' => 'مستخدم',
        'signatories' => [['name' => 'أ', 'title' => 'الأستاذ'], ['name' => 'ب', 'title' => 'رئيس القسم'], ['name' => 'ج', 'title' => 'عميد الكلية']],
    ])->render();

    expect($html)->toContain('رياضيات')->toContain('طالب تجريبي')->toContain('أعمال السنة')->toContain('4.00')->toContain('>80<');
});

it('shows frozen students in memory without touching the database', function () {
    $service = new App\Services\Res\ResultSheetService;

    $frozen = (new Student)->forceFill(['university_no' => 1, 'status_code' => '200400020001'])
        ->setRelation('marks', collect([(new Mark)->forceFill(['subject_code' => 'S1', 'mark' => 70, 'grade' => 'B'])]))
        ->setRelation('recom', collect());
    $normal = (new Student)->forceFill(['university_no' => 2, 'status_code' => '20040001'])
        ->setRelation('marks', collect([(new Mark)->forceFill(['subject_code' => 'S1', 'mark' => 70, 'grade' => 'B'])]))
        ->setRelation('recom', collect());

    $service->overlayFrozen(collect([$frozen, $normal]), ['S1', 'S2']);

    // المجمّد: درجة 0 وتقدير "ت" لكل مادة مختارة، وقرار التجميد
    expect($frozen->marks->pluck('grade')->all())->toBe(['ت', 'ت'])
        ->and($frozen->marks->pluck('mark')->unique()->all())->toBe([0])
        ->and((int) $frozen->recom->first()->recom_code)->toBe(App\Services\Res\ResultSheetService::FROZEN_RECOM);

    // غير المجمّد لا يتغير
    expect($normal->marks->first()->grade)->toBe('B')->and($normal->recom)->toBeEmpty();
});

it('offers a back button only when a back url is given', function () {
    $with = renderResult(2, ['backUrl' => 'http://erp.test/admin/res/dashboard-result']);

    expect($with)->toContain('رجوع')->toContain('href="http://erp.test/admin/res/dashboard-result"')->toContain('function goBack');
    expect(renderResult(2))->not->toContain('رجوع')->not->toContain('function goBack');
});

it('builds a back url to the dashboard that carries the print selections', function () {
    $url = App\Http\Controllers\Res\DashBoardResultController::backUrl([
        'batch' => 2026, 'faculty' => 1003, 'department' => 3005, 'special' => null, 'level' => 1002, 'absent' => 1,
        'subjects' => ['S1', 'S2'], 'fields' => ['name_ar', 'grade'], 'result_type' => 'previous',
        'design' => 'continuous', 'show_history' => 1, 'per_page' => 16,
    ]);

    parse_str(parse_url($url, PHP_URL_QUERY), $query);

    expect(parse_url($url, PHP_URL_PATH))->toEndWith('/admin/res/dashboard-result')
        ->and($query)->toMatchArray([
            'batch' => '2026', 'faculty' => '1003', 'department' => '3005', 'level' => '1002', 'absent' => '1',
            'result_type' => 'previous', 'design' => 'continuous', 'show_history' => '1', 'countstudent' => '16',
        ])
        ->and($query['subjects'])->toBe(['S1', 'S2'])
        ->and($query)->not->toHaveKey('special');
});

it('builds a back url to the entry page that carries the filters', function () {
    $url = App\Filament\Clusters\Res\Pages\EntryMarks\EntryMark::urlWithFilters([
        'batch' => 2026, 'faculty' => 1003, 'department' => null, 'level' => 1002, 'absent' => 2, 'subject' => 55, 'design' => 'continuous',
    ]);

    parse_str(parse_url($url, PHP_URL_QUERY), $query);

    expect(parse_url($url, PHP_URL_PATH))->toEndWith('/admin/res/entry-mark')
        ->and($query['data'])->toBe(['batch' => '2026', 'faculty' => '1003', 'level' => '1002', 'absent' => '2', 'subject' => '55']);
});

it('reads the Arabic university name from the translation file even when the app locale is not Arabic', function () {
    app()->setLocale('en');
    Illuminate\Support\Facades\Lang::addLines(['main.univ_name' => 'جامعة الاختبار'], 'ar');
    Illuminate\Support\Facades\Lang::addLines(['main.univ_name' => 'English University'], 'en');

    $html = renderResult(2);

    expect($html)->toContain('جامعة الاختبار - كلية الاختبار')->not->toContain('English University');
});

it('prints the header and footer titles only when the faculty provides them', function () {
    $with = renderResult(2, ['headerTitle' => 'سطر علوي مخصص', 'footerTitle' => 'نص سفلي مخصص']);

    expect($with)->toContain('سطر علوي مخصص')->toContain('نص سفلي مخصص')
        ->and(strpos($with, 'سطر علوي مخصص'))->toBeLessThan(strpos($with, 'نتيجة الامتحانات'));

    $without = renderResult(2);

    expect($without)->not->toContain('سطر علوي مخصص')->not->toContain('line-clamp-2');
});

it('trims faculty titles and treats blanks as absent', function () {
    $service = new App\Services\Res\ResultSheetService;
    $faculty = (new App\Models\Reg\Faculty)->forceFill(['metadata' => ['header_title' => '  عنوان  ', 'footer_title' => '   ']]);

    expect($service->metaText($faculty, 'header_title'))->toBe('عنوان')
        ->and($service->metaText($faculty, 'footer_title'))->toBeNull()
        ->and($service->metaText($faculty, 'missing'))->toBeNull()
        ->and($service->metaText((new App\Models\Reg\Faculty)->forceFill(['metadata' => ['header_title' => '--']]), 'header_title'))->toBeNull()
        ->and($service->metaText(null, 'header_title'))->toBeNull();
});

it('keeps the university logo path in config/university.php', function () {
    config(['university.logo' => 'logos/missing-logo.png']);

    expect((new App\Services\Res\ResultSheetService)->logos(10)['university'])->toBeNull();
});

it('drops signatories that have no name', function () {
    $service = new App\Services\Res\ResultSheetService;

    $people = $service->withNames([
        ['name' => null, 'title' => 'المسجل'],
        ['name' => '   ', 'title' => 'رئيس القسم'],
        ['name' => ' د. فلان ', 'title' => 'عميد الكلية'],
        ['name' => '', 'title' => 'رئيس المكتب الأكاديمي'],
        ['name' => '--', 'title' => 'مسمى بشرطتين'],
        ['name' => ' — ', 'title' => 'مسمى بشرطة طويلة'],
        ['name' => '...', 'title' => 'مسمى بنقاط'],
    ]);

    expect($people)->toBe([['name' => 'د. فلان', 'title' => 'عميد الكلية']]);
});

it('prints no signature block and reserves no space when every signatory is empty', function () {
    $rowHeight = function (string $html): float {
        preg_match('/<tbody>.*?style="height: ([0-9.]+)mm"/s', $html, $m);

        return (float) $m[1];
    };

    $withSignatures = renderResult(2);
    $without = renderResult(2, ['signatories' => []]);

    expect($withSignatures)->toContain('عميد الكلية')
        ->and($without)->not->toContain('عميد الكلية')->not->toContain('المسجل')->not->toContain('uppercase')
        ->and($rowHeight($without))->toBeGreaterThan($rowHeight($withSignatures));
});

it('still prints the footer title when there are no signatories', function () {
    $html = renderResult(2, ['signatories' => [], 'footerTitle' => 'نص ختامي']);

    expect($html)->toContain('نص ختامي')->not->toContain('عميد الكلية');
});

function renderSubject(array $overrides = []): string
{
    $subject = (new App\Models\Res\Subject)->forceFill(['code' => 5, 'name_ar' => 'رياضيات', 'crch' => 3, 'exam' => 100]);
    foreach (['faculty', 'department', 'special', 'level'] as $relation) {
        $subject->setRelation($relation, null);
    }

    return view('Res.sheets.subject', $overrides + [
        'subject' => $subject, 'design' => 'page_header', 'designs' => DESIGNS, 'perPage' => 16,
        'rows' => collect([['university_no' => 111, 'name' => 'طالب تجريبي', 'mark' => 60, 'cwork' => null, 'total' => 60, 'grade' => 'C', 'point' => 2.5]]),
        'showCwork' => false, 'logos' => ['university' => null, 'faculty' => null],
        'examType' => 'الاساسي', 'batchName' => 'دفعة 2026', 'stage' => 'الأستاذ', 'entered' => 1, 'printedBy' => 'مستخدم',
        'signatories' => [['name' => 'أ. مدخل الدرجات', 'title' => 'مُدخل الدرجات']],
    ])->render();
}

it('never prints the faculty header or footer titles on the subject sheet', function () {
    $html = renderSubject(['headerTitle' => 'سطر علوي للكلية', 'footerTitle' => 'نص سفلي للكلية']);

    expect($html)->not->toContain('سطر علوي للكلية')->not->toContain('نص سفلي للكلية');
});

it('signs the subject sheet with the data-entry user only', function () {
    $html = renderSubject();

    expect($html)->toContain('أ. مدخل الدرجات')->toContain('مُدخل الدرجات')
        ->not->toContain('عميد الكلية')->not->toContain('رئيس القسم')
        ->and(substr_count($html, 'uppercase tracking-wide'))->toBe(1);
});

it('offers current and previous result switching in the result sheet toolbar', function () {
    $current = renderResult(2);
    $previous = renderResult(2, ['isPrevious' => true]);

    expect($current)->toContain('نتيجة حالية')->toContain('نتيجة سابقة')->toContain('result_type=previous')
        ->and($previous)->toContain('result_type=current');
});

it('shows the control panel, extra statistics and the highest gpa like the reference report', function () {
    $html = renderResult(8);

    expect($html)
        ->toContain('id="toggleMarks"')->toContain('id="toggleMarksOnly"')
        ->toContain('id="toggleRecStats"')->toContain('id="toggleSubStats"')
        ->toContain('أعلى معدل')->toContain('3.50')
        ->toContain('إحصائيات التوصيات')->toContain('إحصائيات المواد الدراسية')
        ->toContain('طباعة التقرير');
});

it('keeps grade cells white and greys only the failed ones', function () {
    $html = renderResult(8);

    expect($html)->not->toContain('bg-rose-')->not->toContain('bg-emerald-')->not->toContain('bg-amber-');
    expect($html)->toContain('!bg-gray-300');
});
