<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * اختبارات توصيفية للمسارات والصلاحيات: تثبّت السلوك الحالي كما هو (بما فيه الثغرات المعروفة)
 * حتى يظهر أي تغيير في السلوك عند التنظيف/الإصلاح لاحقاً.
 */
beforeEach(function () {
    foreach ([
        '0001_01_01_000000_create_users_table.php',
        '2026_02_08_143514_create_permission_tables.php',
    ] as $migration) {
        Artisan::call('migrate', ['--path' => "database/migrations/{$migration}", '--no-interaction' => true]);
    }
});

dataset('guest-protected-routes', [
    '/admin',
    '/kassala-result',
    '/analytics',
    '/help/students',
    '/reports/semester-summary',
    '/attendance/report/lecture/1',
    '/invoices/print/1',
    '/transcript/1/ar',
    '/student/1/transcript',
    '/res/entry-mark/print',
]);

it('redirects guests to the panel login page', function (string $uri) {
    $this->get($uri)->assertRedirect('/admin/login');
})->with('guest-protected-routes');

it('serves the panel login page', function () {
    $this->get('/admin/login')->assertOk();
});

it('no longer exposes public self-registration', function () {
    $this->get('/admin/register')->assertNotFound();
});

it('serves certificate verification publicly and answers 404 for unknown hashes', function () {
    Schema::create('cert_students', function (Blueprint $table) {
        $table->id();
        $table->string('verify_hash')->nullable();
        $table->softDeletes();
    });

    $this->get('/verify/unknown-hash')->assertNotFound();
});

it('requires authentication on the card print route and redirects guests', function () {
    $this->get('/cards/1/print')->assertRedirect('/admin/login');
});

it('no longer exposes the test email route', function () {
    $this->get('/send-test-email')->assertNotFound();
});

it('denies panel access to authenticated users outside the local environment', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/admin')->assertForbidden();
    $this->get('/help/students')->assertForbidden();
});

it('lets authenticated users reach help guides when running in the local environment', function () {
    config(['app.env' => 'local']);
    $this->actingAs(User::factory()->create());

    $this->get('/help/students')->assertOk();
    $this->get('/help/entry-marks')->assertOk();
    $this->get('/help/unknown-guide')->assertNotFound();
});

it('grants resource abilities only to users holding the matching Shield permission', function () {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', Student::class))->toBeFalse();

    Permission::findOrCreate('ViewAny:Student', 'web');
    Permission::findOrCreate('Create:Student', 'web');
    $user->givePermissionTo('ViewAny:Student');
    $user = $user->fresh();

    expect(Gate::forUser($user)->allows('viewAny', Student::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', Student::class))->toBeFalse();
});

it('grants permissions through roles', function () {
    Permission::findOrCreate('Create:Student', 'web');
    $role = Role::findOrCreate('registrar', 'web');
    $role->givePermissionTo('Create:Student');

    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect(Gate::forUser($user->fresh())->allows('create', Student::class))->toBeTrue();
});

it('treats the super admin role as an academic super admin', function () {
    $user = User::factory()->create();
    expect($user->isSuperAdmin())->toBeFalse();

    Role::findOrCreate(config('filament-shield.super_admin.name', 'super_admin'), 'web');
    $user->assignRole(config('filament-shield.super_admin.name', 'super_admin'));

    expect($user->fresh()->isSuperAdmin())->toBeTrue();
});

it('denies card printing to authenticated users without the view permission', function () {
    config(['app.env' => 'local']);
    Schema::create('reg_cards', function (Blueprint $table) {
        $table->id();
        $table->string('university_no')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
    DB::table('reg_cards')->insert(['id' => 1, 'university_no' => '1']);
    $this->actingAs(User::factory()->create());

    $this->get('/cards/1/print')->assertForbidden();
});

it('renders the panel right-to-left for the Arabic locale and left-to-right for English', function () {
    app()->setLocale('ar');
    $this->get('/admin/login')->assertOk()->assertSee('dir="rtl"', false);

    app()->setLocale('en');
    $this->get('/admin/login')->assertOk()->assertSee('dir="ltr"', false);
});
