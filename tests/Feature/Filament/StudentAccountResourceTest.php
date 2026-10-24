<?php

use App\Filament\Clusters\Reg\Resources\StudentAccounts\Pages\EditStudentAccount;
use App\Filament\Clusters\Reg\Resources\StudentAccounts\Pages\ListStudentAccounts;
use App\Models\StudentAccount;
use App\Models\User;
use App\Notifications\Student\ResetPasswordNotification;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    migrateErpSchema();
});

function portalAccount(string $email = 'real@example.test', string $universityNo = '20250001'): StudentAccount
{
    return StudentAccount::query()->forceCreate([
        'name' => 'طالب تجريبي', 'email' => $email, 'password' => 'x', 'university_no' => $universityNo,
    ]);
}

it('lists portal accounts for a super admin', function () {
    actingAsSuperAdmin();
    $accounts = collect([portalAccount('a@example.test', '1'), portalAccount('b@example.test', '2')]);

    Livewire::test(ListStudentAccounts::class)->assertCanSeeTableRecords($accounts);
});

it('sends a reset link through the students broker for a deliverable email', function () {
    Notification::fake();
    actingAsSuperAdmin();
    $account = portalAccount();

    Livewire::test(ListStudentAccounts::class)
        ->callAction(TestAction::make('sendResetLink')->table($account))
        ->assertNotified('تم إرسال رابط التعيين.');

    Notification::assertSentTo($account, ResetPasswordNotification::class);
});

it('refuses to send a reset link to the placeholder email created by the admission workflow', function () {
    Notification::fake();
    actingAsSuperAdmin();
    $account = portalAccount('20250001@students.local');

    Livewire::test(ListStudentAccounts::class)
        ->callAction(TestAction::make('sendResetLink')->table($account))
        ->assertNotified('البريد الحالي وهمي؛ عدّل بريد الطالب أولاً.');

    Notification::assertNothingSent();
});

it('lets an admin correct the email but not the name or university number', function () {
    actingAsSuperAdmin();
    $account = portalAccount();

    Livewire::test(EditStudentAccount::class, ['record' => $account->getRouteKey()])
        ->fillForm(['email' => 'fixed@example.test', 'name' => 'اسم آخر', 'university_no' => '999'])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $account->fresh();

    expect($fresh->email)->toBe('fixed@example.test')
        ->and($fresh->name)->toBe('طالب تجريبي')
        ->and((string) $fresh->university_no)->toBe('20250001');
});

it('never allows creating portal accounts from the admin panel', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('ViewAny:StudentAccount', 'web');
    $user->givePermissionTo('ViewAny:StudentAccount');

    expect(Gate::forUser($user)->allows('create', StudentAccount::class))->toBeFalse();
});

it('maps every portal account ability to its permission', function (string $ability, string $permission) {
    $user = User::factory()->create();
    $account = portalAccount();
    Permission::findOrCreate($permission, 'web');

    $subject = $ability === 'viewAny' ? StudentAccount::class : $account;

    expect(Gate::forUser($user)->allows($ability, $subject))->toBeFalse();

    $user->givePermissionTo($permission);

    expect(Gate::forUser($user->fresh())->allows($ability, $subject))->toBeTrue();
})->with([
    ['viewAny', 'ViewAny:StudentAccount'],
    ['view', 'View:StudentAccount'],
    ['update', 'Update:StudentAccount'],
    ['delete', 'Delete:StudentAccount'],
    ['sendResetLink', 'SendResetLink:StudentAccount'],
]);

it('grants the account permissions to every holder of the reset account permission', function () {
    $reset = Permission::findOrCreate('ResetAccount:Student', 'web');
    $role = Spatie\Permission\Models\Role::findOrCreate('registrar', 'web');
    $role->givePermissionTo($reset);
    $other = Spatie\Permission\Models\Role::findOrCreate('other', 'web');

    Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_23_082459_grant_student_account_permissions.php', '--no-interaction' => true]);
    app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    expect($role->fresh()->hasPermissionTo('SendResetLink:StudentAccount'))->toBeTrue()
        ->and($role->fresh()->hasPermissionTo('ViewAny:StudentAccount'))->toBeTrue()
        ->and($other->fresh()->hasPermissionTo('ViewAny:StudentAccount'))->toBeFalse();
});
