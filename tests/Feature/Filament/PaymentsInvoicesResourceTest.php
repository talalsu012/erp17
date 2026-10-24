<?php

use App\Filament\Clusters\Invoices\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Clusters\Invoices\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Clusters\Reg\Resources\Payments\Pages\CreatePayment;
use App\Filament\Clusters\Reg\Resources\Payments\Pages\ListPayments;
use App\Models\Invoice\Invoice;
use App\Models\Reg\Payment;
use Livewire\Livewire;

beforeEach(function () {
    migrateErpSchema();
});

it('lists payments', function () {
    actingAsSuperAdmin();
    $payments = collect([
        Payment::create(['university_no' => 20240001, 'amount' => 500, 'bank' => 'BOK', 'voucher_no' => 'V-1', 'bankdate' => '2026-01-01', 'sys_type' => 'REG']),
        Payment::create(['university_no' => 20240002, 'amount' => 300, 'bank' => 'FIB', 'voucher_no' => 'V-2', 'bankdate' => '2026-01-02', 'sys_type' => 'REG']),
    ]);

    Livewire::test(ListPayments::class)
        ->loadTable()
        ->assertSuccessful()
        ->assertCanSeeTableRecords($payments);
});

it('rejects a payment form with missing required fields', function () {
    actingAsSuperAdmin();

    Livewire::test(CreatePayment::class)
        ->fillForm(['university_no' => null, 'amount' => null, 'voucher_no' => null])
        ->call('create')
        ->assertHasFormErrors(['university_no' => 'required', 'amount' => 'required', 'voucher_no' => 'required']);

    expect(Payment::query()->count())->toBe(0);
});

it('lists invoices', function () {
    actingAsSuperAdmin();
    $invoices = collect([
        Invoice::create(['name_ar' => 'فاتورة 1', 'invoice_no' => '1000001', 'amount' => 100, 'bank_pay' => 0, 'status' => 'pending']),
        Invoice::create(['name_ar' => 'فاتورة 2', 'invoice_no' => '1000002', 'amount' => 200, 'bank_pay' => 0, 'status' => 'pending']),
    ]);

    Livewire::test(ListInvoices::class)
        ->loadTable()
        ->assertSuccessful()
        ->assertCanSeeTableRecords($invoices);
});

it('rejects an invoice form with missing required fields', function () {
    actingAsSuperAdmin();

    Livewire::test(CreateInvoice::class)
        ->fillForm(['invoice_no' => null, 'name_ar' => null, 'amount' => null])
        ->call('create')
        ->assertHasFormErrors(['invoice_no' => 'required', 'name_ar' => 'required', 'amount' => 'required']);

    expect(Invoice::query()->count())->toBe(0);
});
