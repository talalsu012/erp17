<?php

use App\Models\Student;
use App\Models\StudentAccount;
use App\Models\User;
use App\Notifications\Student\ResetPasswordNotification;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);
    RateLimiter::clear('student-register');
});

function studentAccount(array $attributes = []): StudentAccount
{
    $student = Student::factory()->create();

    return StudentAccount::query()->forceCreate(array_merge([
        'name' => $student->name_ar,
        'email' => 'student'.$student->university_no.'@example.test',
        'password' => Hash::make('Passw0rd!'),
        'university_no' => $student->university_no,
    ], $attributes));
}

it('redirects guests from protected portal pages to the student login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('student.login'));
})->with(['student.dashboard', 'student.profile.edit', 'student.photo']);

it('serves the login, register and forgot password pages in Arabic with RTL', function (string $routeName) {
    $this->get(route($routeName))->assertOk()->assertSee('dir="rtl"', false);
})->with(['student.login', 'student.register', 'student.password.request']);

it('sends hardened security headers on portal pages only', function () {
    $this->get(route('student.login'))
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeaderMissing('Strict-Transport-Security');

    $this->get('/admin/login')->assertHeaderMissing('X-Frame-Options');
});

it('logs a student in with the student guard and keeps the admin guard out', function () {
    $account = studentAccount();

    $this->post(route('student.login.store'), ['email' => $account->email, 'password' => 'Passw0rd!'])
        ->assertRedirect(route('student.dashboard', absolute: false));

    $this->assertAuthenticatedAs($account, 'student');
    $this->assertGuest('web');
});

it('rejects wrong credentials and throttles repeated attempts', function () {
    $account = studentAccount();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('student.login.store'), ['email' => $account->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
    }

    $this->post(route('student.login.store'), ['email' => $account->email, 'password' => 'Passw0rd!'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('student');
});

it('logs a student out', function () {
    $account = studentAccount();

    $this->actingAs($account, 'student')
        ->post(route('student.logout'))
        ->assertRedirect(route('student.login'));

    $this->assertGuest('student');
});

it('does not let a student session open the admin panel', function () {
    $this->actingAs(studentAccount(), 'student');

    $this->get('/admin')->assertRedirect('/admin/login');
});

it('does not let an admin session open the student portal', function () {
    config(['app.env' => 'local']);
    $this->actingAs(User::factory()->create(), 'web');

    $this->get(route('student.dashboard'))->assertRedirect(route('student.login'));
});

it('sends authenticated students away from the guest pages', function () {
    $this->actingAs(studentAccount(), 'student')
        ->get(route('student.login'))
        ->assertRedirect(route('student.dashboard'));
});

it('answers password reset requests identically for known and unknown emails', function () {
    Notification::fake();
    $account = studentAccount();

    $known = $this->post(route('student.password.email'), ['email' => $account->email]);
    $unknown = $this->post(route('student.password.email'), ['email' => 'nobody@example.test']);

    $known->assertSessionHas('status', __('student_portal.reset_link_sent'));
    $unknown->assertSessionHas('status', __('student_portal.reset_link_sent'));

    Notification::assertSentTo($account, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($account) {
        $url = $notification->toMail($account)->actionUrl;

        return str_contains($url, '/student/reset-password/') && str_contains($url, urlencode($account->email));
    });
    Notification::assertSentToTimes($account, ResetPasswordNotification::class, 1);
});

it('resets the password with a valid token from the student broker', function () {
    $account = studentAccount();
    $token = Illuminate\Support\Facades\Password::broker('students')->createToken($account);

    $this->post(route('student.password.store'), [
        'token' => $token,
        'email' => $account->email,
        'password' => 'N3wPassword!',
        'password_confirmation' => 'N3wPassword!',
    ])->assertRedirect(route('student.login'));

    expect(Hash::check('N3wPassword!', $account->fresh()->password))->toBeTrue();
    expect(Illuminate\Support\Facades\DB::table('student_password_reset_tokens')->count())->toBe(0);
});

it('keeps student reset tokens separate from admin reset tokens', function () {
    $account = studentAccount();
    Illuminate\Support\Facades\Password::broker('students')->createToken($account);

    expect(Illuminate\Support\Facades\DB::table('student_password_reset_tokens')->where('email', $account->email)->exists())->toBeTrue()
        ->and(Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $account->email)->exists())->toBeFalse();
});

it('allows the Vite dev server in the CSP report only while it is running', function () {
    $this->get(route('student.login'))
        ->assertHeader('Content-Security-Policy-Report-Only')
        ->assertHeaderMissing('X-Vite-Dev');

    expect($this->get(route('student.login'))->headers->get('Content-Security-Policy-Report-Only'))
        ->not->toContain('5173');

    $hotFile = tempnam(sys_get_temp_dir(), 'hot');
    file_put_contents($hotFile, 'http://[::1]:5173');
    Illuminate\Support\Facades\Vite::useHotFile($hotFile);

    $csp = $this->get(route('student.login'))->headers->get('Content-Security-Policy-Report-Only');

    expect($csp)->toContain("script-src 'self' 'unsafe-inline' 'unsafe-eval' http://[::1]:5173 ws://[::1]:5173")
        ->and($csp)->toContain("connect-src 'self' http://[::1]:5173 ws://[::1]:5173");

    unlink($hotFile);
});

it('links the login page to the registration page', function () {
    $this->get(route('student.login'))
        ->assertOk()
        ->assertSee(route('student.register'), false);
});

it('takes the university name and logo from config/university.php', function () {
    config(['university.name_ar' => 'جامعة الاختبار', 'university.logo' => 'logos/test-logo.png']);

    $this->get(route('student.login'))
        ->assertOk()
        ->assertSee('جامعة الاختبار')
        ->assertSee('logos/test-logo.png', false);

    expect(__('main.univ_name', [], 'ar'))->toBe('جامعة الاختبار');
});

it('shows an Arabic page when the student portal throttles a browser request', function () {
    config(['student.rate_limits.verify_per_university_no_per_hour' => 1]);

    $response = null;
    foreach (range(1, 2) as $attempt) {
        $response = $this->post(route('student.register.verify'), ['university_no' => '777']);
    }

    $response->assertStatus(429)->assertSee('محاولات كثيرة');
});
