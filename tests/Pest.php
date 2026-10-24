<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * يهيّئ مخطط SQLite من النسخة المستخرجة من قاعدة البيانات الفعلية (tests/Support/erp_schema.php)،
 * لأن الـ migrations وحدها لا تنشئ كل الجداول والأعمدة (مثل invoices وreg_levels.parent_code).
 */
function migrateErpSchema(): void
{
    foreach (require __DIR__.'/Support/erp_schema.php' as $statement) {
        Illuminate\Support\Facades\DB::statement($statement);
    }

    Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_10_23_081344_add_student_portal_auth_tables.php',
        '--no-interaction' => true,
    ]);

    Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_10_24_093300_add_files_to_users_std_table.php',
        '--no-interaction' => true,
    ]);
}

/**
 * مستخدم بدور super_admin ويعمل داخل البيئة المحلية (شرط دخول لوحة Filament عند غياب FilamentUser).
 */
function actingAsSuperAdmin(): App\Models\User
{
    config(['app.env' => 'local']);

    $role = Spatie\Permission\Models\Role::findOrCreate(config('filament-shield.super_admin.name'), 'web');
    $user = App\Models\User::factory()->create();
    $user->assignRole($role);

    Illuminate\Support\Facades\Gate::before(fn ($authUser) => $authUser->hasRole(config('filament-shield.super_admin.name')) ? true : null);

    test()->actingAs($user);
    Filament\Facades\Filament::setCurrentPanel('admin');

    return $user;
}
