<?php

use App\Models\Api\ApiUser;
use App\Models\Reg\Payment;
use App\Models\Student;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    migrateErpSchema();
    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_10_24_045818_create_usersapi_table_and_add_transaction_id_to_payments.php',
        '--no-interaction' => true,
    ]);
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);

    ApiUser::query()->create([
        'name' => 'Bank', 'email' => 'bank@example.test', 'password' => Hash::make('Passw0rd!'),
        'bank' => 'ONB', 'university' => 'SABFS', 'ip' => '127.0.0.1',
    ]);
    $this->student = Student::factory()->create(['pay1' => 400, 'pay2' => 0, 'pay3' => 0, 'pay4' => 0]);
    openRegistrationFor($this->student);
    $this->token = $this->postJson('/apibank/login', ['email' => 'bank@example.test', 'password' => 'Passw0rd!'])->json('token');
    $this->auth = ['Authorization' => "Bearer {$this->token}"];
});

it('logs in with the legacy messages', function () {
    expect($this->token)->toBeString();

    $this->postJson('/apibank/login.php', ['email' => 'nobody@example.test', 'password' => 'Passw0rd!'])
        ->assertStatus(422)->assertJson(['message' => 'Invalid Email Address!']);
});

it('answers a student inquiry from the Stdid header', function () {
    $this->getJson('/apibank/APITIQ', [...$this->auth, 'Stdid' => (string) $this->student->university_no])
        ->assertOk()
        ->assertExactJson([
            'UNI' => 'SABFS', 'SYSTYPE' => 'REGISTRATION',
            'FAC' => $this->student->faculty->name_ar, 'FACID' => $this->student->faculty_code,
            'STDID' => $this->student->university_no, 'NAME' => $this->student->name_ar, 'AMT' => 400,
        ]);

    $this->getJson('/apibank/APITIQ', [...$this->auth, 'Stdid' => '0'])
        ->assertStatus(404)->assertExactJson(['success' => 0, 'status' => 404, 'message' => 'Student Not Found!']);

    $this->getJson('/apibank/APITIQ', $this->auth)->assertStatus(422);
});

it('reports token problems in the legacy shapes', function () {
    $this->getJson('/apibank/APITIQ', ['Stdid' => '1'])
        ->assertOk()->assertExactJson(['status' => 500, 'success' => 0, 'message' => 'Token not found in request']);

    $this->getJson('/apibank/APITIQ', ['Authorization' => 'Bearer a.b.c', 'Stdid' => '1'])
        ->assertOk()->assertExactJson(['status' => 404, 'success' => 0, 'message' => 'Signature verification failed']);

    $this->getJson('/apibank/APITIQ', ['X-Auth-Token' => $this->token, 'Stdid' => (string) $this->student->university_no])
        ->assertOk()->assertJson(['SYSTYPE' => 'REGISTRATION']);
});

it('posts a payment and finds it again by voucher', function () {
    $payload = ['STUDENTSID' => (string) $this->student->university_no, 'AMOUNT' => '400', 'BANKDATE' => '2026-10-24', 'VOUCHERNO' => 'V-100', 'BRANCH' => 'Main'];

    $this->postJson('/apibank/APITIQ', $payload, $this->auth)
        ->assertOk()->assertJson(['success' => 1, 'status' => 200]);

    expect(Payment::query()->where('voucher_no', 'V-100')->first())->bank->toBe('ONB')->branch->toBe('Main');

    $this->postJson('/apibank/APITIQ', ['AMOUNT' => '1'], $this->auth)
        ->assertStatus(422)->assertJson(['fields' => ['STUDENTSID', 'AMOUNT', 'VOUCHERNO', 'BANKDATE', 'branch']]);

    $this->putJson('/apibank/APITIQ', ['VOUCHER' => 'V-100'], $this->auth)
        ->assertOk()->assertJson(['VOUCHER' => 'V-100', 'BRANCH' => 'Main', 'DONE' => 1, 'BANK' => 'ONB', 'UNIVERSITY' => 'SABFS']);

    $this->putJson('/apibank/APITIQ', ['VOUCHER' => 'nope'], $this->auth)->assertStatus(404);
});

it('answers other methods with the legacy not found message', function () {
    $this->deleteJson('/apibank/APITIQ')->assertStatus(404)->assertJson(['message' => 'Page Not Found!']);
});

it('hides students whose registration is closed from the inquiry', function () {
    $this->student->forceFill(['reg_access' => App\Reg\RegAccess::Closed])->saveQuietly();

    $this->getJson('/apibank/APITIQ', [...$this->auth, 'Stdid' => (string) $this->student->university_no])
        ->assertStatus(404);
});
