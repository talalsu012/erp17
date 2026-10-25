<?php

use App\Models\Api\ApiUser;
use App\Models\Reg\Payment;
use App\Models\Student;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    migrateErpSchema();
    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_10_24_045818_create_usersapi_table_and_add_transaction_id_to_payments.php',
        '--no-interaction' => true,
    ]);
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);
    RateLimiter::clear('throttle');

    $this->bank = ApiUser::query()->create([
        'name' => 'BOK', 'email' => 'bank@example.test', 'password' => Hash::make('Passw0rd!'),
        'bank' => 'BOK', 'university' => 'SABFS', 'ip' => '127.0.0.1',
    ]);
    $this->student = Student::factory()->create(['pay1' => 0, 'pay2' => 250, 'pay3' => 0, 'pay4' => 0]);
    openRegistrationFor($this->student);
    $this->token = $this->postJson('/apitiq/login', ['email' => 'bank@example.test', 'password' => 'Passw0rd!'])->json('token');
});

it('logs a bank user in and returns a JWT', function () {
    expect($this->token)->toBeString()->and(explode('.', $this->token))->toHaveCount(3);
});

it('rejects invalid login attempts with the legacy messages', function () {
    $this->postJson('/apitiq/login', ['email' => 'bank@example.test'])->assertStatus(422)->assertJson(['message' => 'Please Fill in all Required Fields!', 'fields' => ['email', 'password']]);
    $this->postJson('/apitiq/login', ['email' => 'bad', 'password' => 'Passw0rd!'])->assertJson(['message' => 'Invalid Email Address!']);
    $this->postJson('/apitiq/login', ['email' => 'bank@example.test', 'password' => 'short'])->assertJson(['message' => 'Your password must be at least 8 characters long!']);
    $this->postJson('/apitiq/login', ['email' => 'bank@example.test', 'password' => 'Wrong-pass1'])->assertJson(['message' => 'Invalid Password!']);
});

it('keeps bank registration closed unless enabled', function () {
    config(['services.bok.allow_registration' => false]);
    $this->postJson('/apitiq/register', [])->assertStatus(404);

    config(['services.bok.allow_registration' => true]);
    $payload = ['name' => 'New Bank', 'email' => 'new@example.test', 'password' => 'Passw0rd!', 'university' => 'U', 'bank' => 'NB', 'ip' => '1.1.1.1'];
    $this->postJson('/apitiq/register', $payload)->assertStatus(201)->assertJson(['success' => 1]);
    $this->postJson('/apitiq/register', $payload)->assertStatus(422)->assertJson(['message' => 'This E-mail already in use!']);
});

it('requires a bearer token on the payment endpoints', function () {
    $this->postJson('/apitiq/inquiry', ['unique_id' => '1'])->assertOk()->assertExactJson(['status' => '33', 'msg' => 'Unauthorized Request']);
    $this->postJson('/apitiq/inquiry', ['unique_id' => '1'], ['Authorization' => 'Bearer bad.token.value'])->assertExactJson(['status' => '13', 'msg' => 'system error Bearer']);
});

it('answers an inquiry with the amount due', function () {
    $this->postJson('/apitiq/inquiry', ['unique_id' => (string) $this->student->university_no, 'transactionId' => 'T1'], ['Authorization' => "Bearer {$this->token}"])
        ->assertOk()
        ->assertExactJson([
            'unique_id' => (string) $this->student->university_no, 'transaction_id' => 'T1',
            'fac' => $this->student->faculty->name_ar, 'facid' => $this->student->faculty_code, 'name' => $this->student->name_ar,
            'admissionid' => $this->student->admission_code, 'admission' => $this->student->admission?->name_ar,
            'amount' => '250', 'minAmount' => '250', 'maxAmount' => '250',
            'status' => '00', 'statusDescription' => 'Successful inquiry',
        ]);

    $this->postJson('/apitiq/inquiry', ['unique_id' => '0'], ['Authorization' => "Bearer {$this->token}"])
        ->assertExactJson(['status' => '11', 'msg' => 'Invalid client ID']);
});

it('posts a payment once, then reconciles and looks it up', function () {
    $headers = ['Authorization' => "Bearer {$this->token}"];
    $payload = ['unique_id' => (string) $this->student->university_no, 'RRN' => 'RRN-1', 'transactionId' => 'T1', 'Amount' => '250'];

    $this->postJson('/apitiq/payment', $payload, $headers)
        ->assertExactJson(['transaction_id' => 'T1', 'amount' => '250', 'status' => '00', 'statusDescription' => 'Posted Successfully']);

    expect(Payment::query()->where('voucher_no', 'RRN-1')->first())
        ->bank->toBe('BOK')->sys_type->toBe('BOK_API')->transaction_id->toBe('T1');

    $this->postJson('/apitiq/payment', $payload, $headers)->assertExactJson(['status' => '12', 'msg' => 'This payment has already been made']);
    $this->postJson('/apitiq/payment', ['RRN' => 'x'], $headers)->assertExactJson(['status' => '13', 'msg' => 'system error missing required fields']);

    $this->postJson('/apitiq/reconcile', ['RRN' => 'RRN-1', 'transactionId' => 'T1'], $headers)
        ->assertJson(['status' => '00', 'statusDescription' => 'Transaction found', 'RRN' => 'RRN-1']);
    $this->postJson('/apitiq/reconcile', ['RRN' => 'nope', 'transactionId' => 'T1'], $headers)
        ->assertExactJson(['status' => '14', 'msg' => 'Transaction not found']);

    $this->putJson('/apitiq/checkPay', ['VOUCHER' => 'RRN-1'], $headers)->assertOk()->assertJson(['VOUCHER' => 'RRN-1', 'BANK' => 'BOK']);
    $this->putJson('/apitiq/checkPay', ['VOUCHER' => 'nope'], $headers)->assertStatus(404);
});

it('hides students whose registration is closed from the inquiry', function () {
    $this->student->forceFill(['reg_access' => App\Reg\RegAccess::Closed])->saveQuietly();

    $this->postJson('/apitiq/inquiry', ['unique_id' => (string) $this->student->university_no], ['Authorization' => "Bearer {$this->token}"])
        ->assertJson(['status' => '11']);
});
