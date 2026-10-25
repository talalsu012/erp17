<?php

use App\Http\Controllers\Admin\StudentDocumentController;
use App\Http\Controllers\Attendance\AttendanceReportController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Res\DashBoardResultController;
use App\Http\Controllers\Res\EntryMarkPrintController;
use App\Http\Controllers\Res\PrintSummaryController;
use App\Http\Controllers\Res\ReportController;
use App\Http\Controllers\Res\StudentResultsController;
use App\Http\Controllers\Res\TranscriptController;
use App\Models\Invoice\Invoice;
use App\Models\Reg\Card;
use App\Services\Card\StudentCardService;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

// Certificate
Route::get('/verify/{hash}', [QRCodeController::class, 'verify'])
    ->name('certificate.verify')
    ->middleware('throttle:30,1');

Route::get('/certificates/{certificate}/affidavit', [CertificateController::class, 'affidavit'])
    ->name('certificates.affidavit')
    ->middleware('auth');
e('certificate.download');

Route::get('/transcript/{universityNo}/{lang}', [CertificateController::class, 'generate_details'])
    ->name('transcript.details')
    ->middleware([Authenticate::class]);

Route::get('/certificates/{certificate}/generate', [CertificateController::class, 'generate'])
    ->name('certificates.generate')
    ->middleware('auth');
e('certificate.download');

// print card
Route::get('/cards/{card}/print', function (Card $card) {
    Gate::authorize('view', $card);

    $file = StudentCardService::generate($card);

    return response()->file($file);

})->name('cards.print')->middleware([Authenticate::class]);

Route::get('/', fn () => view('welcome'));
// NEW CONTROLLER-----------------------------------------------------------
Route::get('/kassala-result', [DashBoardResultController::class, 'index'])
    ->name('kassala.result')
    ->middleware([Authenticate::class]);

Route::get('/student/{university_no}/transcript', [TranscriptController::class, 'printTranscript'])
    ->name('student.transcript.print')
    ->middleware([Authenticate::class]);

Route::get('/res/entry-mark/print', EntryMarkPrintController::class)
    ->name('res.entry-mark.print')
    ->middleware([Authenticate::class]);

Route::get('/analytics', \App\Http\Controllers\AnalyticsDashboardController::class)
    ->name('analytics.dashboard')
    ->middleware([Authenticate::class]);

Route::get('/help/{guide}', \App\Http\Controllers\HelpController::class)
    ->name('help.show')
    ->middleware([Authenticate::class]);

Route::get('/reports/semester-summary', [ReportController::class, 'semesterSummary'])
    ->name('reports.result_summary')
    ->middleware([Authenticate::class]);

Route::get('/reports/year-result', [ReportController::class, 'yearResult'])
    ->name('reports.year_result')
    ->middleware([Authenticate::class]);

// ------------------------------------------------------------------------
Route::get('/student-results/{dataForm}', [StudentResultsController::class, 'showResults'])
    ->name('student.results')
    ->middleware([Authenticate::class]);
// ------------------------------------------------------------------------
Route::get('/results-summary/{dataForm}', [PrintSummaryController::class, 'showSmmarySubjects'])
    ->name('results.summary')
    ->middleware([Authenticate::class]);
// ------------------------------------------------------------------------

Route::middleware([Authenticate::class])->prefix('attendance')->group(function () {

    Route::get('/report/lecture/{lectureID}', [AttendanceReportController::class, 'ShowStudentAttendanceReport'])
        ->name('report.lecture.attendance');
    // additional attendance-related routes can go here

});

Route::middleware([Authenticate::class])->prefix('invoices')->group(function () {

    Route::get('/print/{invoice}', fn (Invoice $invoice) => view('invoice', compact('invoice')))->name('invoice.print');
    // additional invoice-related routes can go here
});

Route::get('admin-files/students/{student:university_no}/photo', [StudentDocumentController::class, 'photo'])
    ->name('students.photo.show')
    ->middleware([Authenticate::class]);

Route::middleware([Authenticate::class])->prefix('admin-files/students/{student:university_no}/documents')->group(function () {
    Route::get('{document}', [StudentDocumentController::class, 'show'])->name('students.documents.show');
    Route::get('{document}/download', [StudentDocumentController::class, 'download'])->name('students.documents.download');
});
