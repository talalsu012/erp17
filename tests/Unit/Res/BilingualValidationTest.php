<?php

use App\Helpers\Bilingual;
use App\Http\Requests\Res\EntryMarkPrintRequest;
use App\Http\Requests\Res\ResultSheetRequest;
use Tests\TestCase;

uses(TestCase::class);

it('returns the message in both languages', function () {
    expect(Bilingual::msg('res_validation.university_no_required'))
        ->toBe('يجب اختيار الطالب لطباعة سجله الأكاديمي. / Please select the student to print their academic record.');
});

it('has the same keys in both languages', function () {
    $ar = require base_path('lang/ar/res_validation.php');
    $en = require base_path('lang/en/res_validation.php');

    expect(array_keys($ar))->toBe(array_keys($en));
});

it('uses bilingual messages in the print form requests', function () {
    foreach ([new ResultSheetRequest, new EntryMarkPrintRequest] as $request) {
        expect($request->messages()['batch.required'])->toContain('يجب اختيار العام الدراسي.')->toContain('Please select the academic year.');
    }
});
