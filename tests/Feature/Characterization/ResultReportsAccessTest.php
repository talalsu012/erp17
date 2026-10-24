<?php

use App\Models\Reg\Faculty;
use App\Models\User;

beforeEach(function () {
    migrateErpSchema();
    config(['app.env' => 'local']);
    Illuminate\Support\Facades\DB::table('reg_levels')->insert(['code' => 1, 'name_ar' => 'المستوى الأول', 'name_en' => 'Level 1']);
});

function encodedReportParams(array $data): string
{
    return base64_encode(json_encode($data));
}

it('rejects malformed report parameters with 400', function (string $uri) {
    actingAsSuperAdmin();

    $this->get($uri.encodedReportParams(['faculty' => 1]))->assertStatus(400);
})->with(['/student-results/', '/results-summary/']);

it('denies result reports of a faculty the user is not assigned to', function (string $uri, array $payload) {
    $this->actingAs(User::factory()->create());
    Faculty::query()->insert(['code' => 10, 'name_ar' => 'كلية']);

    $this->get($uri.encodedReportParams($payload))->assertForbidden();
})->with([
    'student results' => ['/student-results/', ['faculty' => 10, 'level' => 1, 'semester' => 1, 'batch' => 2024, 'absent' => 1]],
    'results summary' => ['/results-summary/', ['faculty' => 10, 'level' => 1, 'batch' => 2024, 'examType' => 1]],
]);

it('lets a user assigned to the faculty past the authorization check', function (string $uri, array $payload) {
    $user = actingAsSuperAdmin();
    Faculty::query()->insert(['code' => 10, 'name_ar' => 'كلية']);
    $user->faculties()->attach(10);

    $response = $this->get($uri.encodedReportParams($payload));

    expect($response->status())->not->toBe(403);
})->with([
    'student results' => ['/student-results/', ['faculty' => 10, 'level' => 1, 'semester' => 1, 'batch' => 2024, 'absent' => 1]],
    'results summary' => ['/results-summary/', ['faculty' => 10, 'level' => 1, 'batch' => 2024, 'examType' => 1]],
]);
