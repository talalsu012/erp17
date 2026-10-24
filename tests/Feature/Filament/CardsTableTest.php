<?php

use App\Filament\Clusters\Reg\Resources\Cards\Pages\ListCards;
use App\Models\Reg\Card;
use App\Models\Student;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Livewire\Livewire;

beforeEach(function () {
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);
});

it('lists cards with the student photo or the default avatar', function () {
    actingAsSuperAdmin();

    $student = Student::factory()->create(['photo' => null]);
    Card::query()->create([
        'university_no' => $student->university_no, 'card_number' => 'C-1', 'issue_date' => now()->toDateString(),
    ]);
    $card = Card::query()->firstOrFail();

    Livewire::test(ListCards::class)->assertSuccessful()->assertCanSeeTableRecords([$card]);
});

it('opens the card preview modal from the table', function () {
    actingAsSuperAdmin();

    $student = Student::factory()->create(['photo' => null]);
    Card::query()->create([
        'university_no' => $student->university_no, 'card_number' => 'C-2', 'issue_date' => now()->toDateString(),
    ]);
    $card = Card::query()->firstOrFail();

    Livewire::test(ListCards::class)
        ->mountTableAction('previewCard', $card)
        ->assertSuccessful();
});

it('keeps the generated id on a newly created card so the redirect to its edit page works', function () {
    $student = Student::factory()->create();

    $card = Card::query()->create([
        'university_no' => $student->university_no, 'card_number' => 'C-3', 'issue_date' => now()->toDateString(),
    ]);

    expect($card->getKey())->not->toBeNull();
});

it('prints the configured university logo on the FARGO card front', function () {
    $html = view('cards.card_template', [
        'card' => new Card,
        'qrCode' => base64_encode('<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
        'data' => [
            'logo' => 'data:image/png;base64,LOGO-DATA', 'avatar' => null, 'title' => 'جامعة تجريبية',
            'name_ar' => 'طالب', 'faculty' => '', 'level' => '', 'university_no' => '1', 'card_number' => 'C-1', 'expiry_date' => '',
            ...array_fill_keys(['active_status', 'content', 'full_name_label', 'in_card', 'in_come', 'in_come_card', 'std_card_expi_label', 'std_card_label', 'std_level_label', 'std_prog_label', 'std_status_label', 'stu_num_label'], 'x'),
        ],
    ])->render();

    expect($html)->toContain('data:image/png;base64,LOGO-DATA')->not->toContain('wikimedia');
})->skip(fn () => ! view()->exists('cards.card_template'));
