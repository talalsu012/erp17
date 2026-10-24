<?php

use App\Models\Invoice\PaymentLogApi;
use App\Models\Reg\Payment;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

/**
 * اختبارات توصيفية: تثبّت سلوك API المدفوعات كما هو الآن (وليس كما يجب أن يكون).
 */
beforeEach(function () {
    foreach ([
        '0001_01_01_000000_create_users_table.php',
        '2026_02_08_143514_create_permission_tables.php',
        '2026_02_12_083204_create_activity_log_table.php',
        '2026_02_12_083205_add_event_column_to_activity_log_table.php',
        '2026_02_12_083206_add_batch_uuid_column_to_activity_log_table.php',
        '2026_02_12_113252_create_personal_access_tokens_table.php',
        '2026_02_16_213708_create_payment_log_apis_table.php',
        '2026_10_21_074447_create_admission_workflows_table.php',
    ] as $migration) {
        Artisan::call('migrate', ['--path' => "database/migrations/{$migration}", '--no-interaction' => true]);
    }

    Schema::create('reg_faculties', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar')->nullable();
        $table->softDeletes();
    });
    Schema::create('reg_ltcolumns', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('code')->nullable();
        $table->unsignedBigInteger('grouped')->nullable();
        $table->string('name_ar')->nullable();
    });
    Schema::create('students', function (Blueprint $table) {
        $table->string('university_no')->primary();
        $table->string('name_ar')->nullable();
        $table->unsignedBigInteger('faculty_code')->nullable();
        $table->unsignedBigInteger('batch_code')->nullable();
        $table->unsignedBigInteger('level_code')->nullable();
        $table->decimal('pay1', 12, 2)->default(0);
        $table->decimal('pay2', 12, 2)->default(0);
        $table->decimal('pay3', 12, 2)->default(0);
        $table->decimal('pay4', 12, 2)->default(0);
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->string('name_ar')->nullable();
        $table->string('invoice_no')->unique();
        $table->decimal('amount', 12, 2)->default(0);
        $table->unsignedBigInteger('sys_type')->nullable();
        $table->unsignedBigInteger('faculty_code')->nullable();
        $table->string('description')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->boolean('bank_pay')->default(false);
        $table->dateTime('due_date')->nullable();
        $table->dateTime('expire_at')->nullable();
        $table->string('status')->nullable();
        $table->string('bank')->nullable();
        $table->dateTime('bankdate')->nullable();
        $table->string('voucher_no')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->string('university_no');
        $table->decimal('amount', 12, 2);
        $table->string('currency')->nullable();
        $table->date('bankdate')->nullable();
        $table->string('bank')->nullable();
        $table->string('branch')->nullable();
        $table->string('voucher_no')->unique();
        $table->unsignedBigInteger('batch_code')->nullable();
        $table->unsignedBigInteger('level_code')->nullable();
        $table->string('sys_type')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });

    DB::table('reg_faculties')->insert(['code' => 10, 'name_ar' => 'كلية تجريبية']);
    DB::table('students')->insert([
        'university_no' => '20240001', 'name_ar' => 'طالب تجريبي', 'faculty_code' => 10,
        'batch_code' => 2024, 'level_code' => 1, 'pay1' => 500, 'pay2' => 300,
    ]);
    DB::table('invoices')->insert([
        'name_ar' => 'فاتورة', 'invoice_no' => '1000001', 'amount' => 750, 'faculty_code' => 10,
        'bank_pay' => 0, 'status' => 'pending',
    ]);

    Role::findOrCreate('BankApiAccess', 'web');
    $this->bankUser = User::factory()->create(['name' => 'Bank Branch', 'email' => 'bank@example.test']);
    $this->bankUser->assignRole('BankApiAccess');
});

it('rejects unauthenticated api calls', function () {
    $this->getJson('/api/APITIQ/20240001')->assertUnauthorized();
    $this->postJson('/api/APITIQ', [])->assertUnauthorized();
});

it('issues a token only to users holding the BankApiAccess role', function () {
    $this->postJson('/api/login', ['email' => 'bank@example.test', 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonStructure(['data' => ['access_token', 'expires_at', 'expires_in_seconds']]);

    $plain = User::factory()->create(['email' => 'plain@example.test']);
    $this->postJson('/api/login', ['email' => $plain->email, 'password' => 'password'])
        ->assertForbidden()
        ->assertJsonPath('status', 'error');
});

it('rejects wrong credentials and invalid login payloads', function () {
    $this->postJson('/api/login', ['email' => 'bank@example.test', 'password' => 'wrong'])
        ->assertUnauthorized()
        ->assertJsonPath('status', 'error');

    $this->postJson('/api/login', ['email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('returns fee data for a student and for an invoice, and 404 for unknown ids', function () {
    Sanctum::actingAs($this->bankUser);

    $this->getJson('/api/APITIQ/20240001')
        ->assertOk()
        ->assertJsonPath('data.UNIVERSITYNO', '20240001')
        ->assertJsonPath('data.AMOUNT', 500)
        ->assertJsonPath('data.FACULTY', 'كلية تجريبية')
        ->assertJsonPath('data.SYS-TYPE', 'REG');

    $this->getJson('/api/APITIQ/1000001')
        ->assertOk()
        ->assertJsonPath('data.AMOUNT', 750);

    $this->getJson('/api/APITIQ/999')->assertNotFound();
});

it('records a student payment and logs the call', function () {
    Sanctum::actingAs($this->bankUser);

    $this->postJson('/api/APITIQ', [
        'university_no' => '20240001', 'amount' => 500, 'bank' => 'BOK', 'voucher_no' => 'V-1',
    ])
        ->assertCreated()
        ->assertJsonPath('data.VOUCHER', 'V-1');

    $payment = Payment::query()->where('voucher_no', 'V-1')->first();
    expect($payment)->not->toBeNull()
        ->and($payment->university_no)->toBe('20240001')
        ->and($payment->branch)->toBe('Bank Branch')
        ->and($payment->sys_type)->toBe('REG');

    expect(PaymentLogApi::query()->where('response_code', 201)->count())->toBe(1);
});

it('validates student payments and rejects duplicate vouchers', function () {
    Sanctum::actingAs($this->bankUser);

    $this->postJson('/api/APITIQ', ['university_no' => '20240001'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount', 'bank', 'voucher_no']);

    $payload = ['university_no' => '20240001', 'amount' => 100, 'bank' => 'BOK', 'voucher_no' => 'V-DUP'];
    $this->postJson('/api/APITIQ', $payload)->assertCreated();
    $this->postJson('/api/APITIQ', $payload)->assertUnprocessable()->assertJsonValidationErrors(['voucher_no']);

    expect(Payment::query()->count())->toBe(1);
});

it('returns 404 and logs when the payer does not exist', function () {
    Sanctum::actingAs($this->bankUser);

    $this->postJson('/api/APITIQ', ['university_no' => '555', 'amount' => 1, 'bank' => 'X', 'voucher_no' => 'V'])
        ->assertNotFound()
        ->assertJsonPath('status', 'error');

    expect(PaymentLogApi::query()->where('response_code', 404)->count())->toBe(1);
});

it('marks an invoice as paid once and refuses a second payment', function () {
    Sanctum::actingAs($this->bankUser);

    $this->postJson('/api/APITIQ', ['university_no' => '1000001', 'amount' => 750, 'bank' => 'BOK', 'voucher_no' => 'INV-1'])
        ->assertOk()
        ->assertJsonPath('message', 'تم تحديث الفاتورة بنجاح');

    $row = DB::table('invoices')->where('invoice_no', '1000001')->first();
    expect((int) $row->bank_pay)->toBe(1)
        ->and($row->status)->toBe('paid')
        ->and($row->voucher_no)->toBe('INV-1');

    $this->postJson('/api/APITIQ', ['university_no' => '1000001', 'amount' => 750, 'bank' => 'BOK', 'voucher_no' => 'INV-2'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'الفاتورة مدفوعة مسبقاً');

    expect(DB::table('invoices')->where('invoice_no', '1000001')->value('voucher_no'))->toBe('INV-1');
});

it('validates invoice payment fields and leaves the invoice unpaid when invalid', function () {
    Sanctum::actingAs($this->bankUser);

    $this->postJson('/api/APITIQ', ['university_no' => '1000001'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount', 'bank', 'voucher_no']);

    expect((int) DB::table('invoices')->where('invoice_no', '1000001')->value('bank_pay'))->toBe(0);
});

it('logs a student payment and its audit row atomically', function () {
    Sanctum::actingAs($this->bankUser);

    $this->postJson('/api/APITIQ', ['university_no' => '20240001', 'amount' => 50, 'bank' => 'BOK', 'voucher_no' => 'V-ATOMIC'])
        ->assertCreated();

    expect(Payment::query()->where('voucher_no', 'V-ATOMIC')->count())->toBe(1)
        ->and(PaymentLogApi::query()->where('response_code', 201)->count())->toBe(1);
});

it('throttles repeated api login attempts', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/login', ['email' => 'bank@example.test', 'password' => 'wrong'])->assertUnauthorized();
    }

    $this->postJson('/api/login', ['email' => 'bank@example.test', 'password' => 'password'])
        ->assertStatus(429);
});

it('issues tokens limited to payment abilities', function () {
    $token = $this->postJson('/api/login', ['email' => 'bank@example.test', 'password' => 'password'])
        ->assertOk()
        ->json('data.access_token');

    $abilities = $this->bankUser->tokens()->first()->abilities;

    expect($token)->toBeString()
        ->and($abilities)->toBe(['payments:read', 'payments:write']);
});
