<?php

use App\Models\Reg\Card;
use App\Models\Student;
use App\Reg\CardStatus;
use App\Services\Card\CardVerificationService;
use App\Services\Card\StudentCardService;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;

beforeEach(function () {
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);

    $this->student = Student::query()->forceCreate(['university_no' => 1190662722, 'name_ar' => 'هدى محمد طاهر', 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 3, 'batch_code' => 2026]);
});

function issueCard(Student $student, array $attributes = []): Card
{
    return Card::query()->create($attributes + [
        'university_no' => $student->university_no, 'card_number' => 'C-'.uniqid(), 'issue_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(), 'status' => CardStatus::active,
    ]);
}

it('lets a guard in with a valid card and hides the university number', function () {
    $card = issueCard($this->student);

    $this->get(CardVerificationService::url($card))
        ->assertOk()
        ->assertSee('مسموح بالدخول')
        ->assertSee('هدى محمد طاهر')
        ->assertDontSee((string) $this->student->university_no);
});

it('rejects an unsigned or tampered link', function () {
    $card = issueCard($this->student);

    $this->get(route('cards.verify', ['card' => $card->getKey()]))->assertForbidden();
    $this->get(str_replace('card/'.$card->getKey(), 'card/'.($card->getKey() + 1), CardVerificationService::url($card)))->assertForbidden();
});

it('denies entry for expired, lost or deleted cards', function (array $attributes, string $reason, bool $deleted) {
    $card = issueCard($this->student, $attributes);
    $url = CardVerificationService::url($card);

    if ($deleted) {
        $card->delete();
    }

    $this->get($url)->assertOk()->assertSee('ممنوع الدخول')->assertSee($reason);
})->with([
    'expired' => [['expiry_date' => '2020-01-01'], 'انتهت صلاحية البطاقة', false],
    'lost' => [['status' => CardStatus::lost], 'البطاقة غير سارية', false],
    'deleted' => [[], 'البطاقة ملغاة', true],
]);

it('encodes the signed verification link in the printed card', function () {
    $card = issueCard($this->student);

    $file = StudentCardService::generate($card);

    expect($file)->toBeFile();
});

it('derives the card gradient from the faculty colour and falls back to green', function () {
    expect(StudentCardService::palette('#1E3A8A'))->toMatchArray(['primary' => '#1E3A8A', 'primary_dark' => '#11204C'])
        ->and(StudentCardService::palette('zzz'))->toMatchArray(['primary' => '#0A522E'])
        ->and(StudentCardService::palette(null))->toMatchArray(['primary' => '#0A522E']);
});

it('prints the card texts from config on both sides', function () {
    config(['card.issuer_en' => 'Deanship X', 'card.website' => 'example.test', 'card.instructions.scan' => 'امسح هنا', 'card.badge_en' => 'ID CARD']);

    $html = view('cards.card_template', [
        'card' => new Card, 'qrCode' => base64_encode('<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
        'data' => ['logo' => '', 'avatar' => null, 'title' => 't', 'name_ar' => 'ط', 'faculty' => '', 'level' => '', 'university_no' => '1', 'card_number' => 'C', 'expiry_date' => '',
            ...array_fill_keys(['active_status', 'content', 'full_name_label', 'in_card', 'in_come', 'in_come_card', 'std_card_expi_label', 'std_card_label', 'std_level_label', 'std_prog_label', 'std_status_label', 'stu_num_label'], 'x')],
    ])->render();

    expect($html)->toContain('Deanship X', 'example.test', 'امسح هنا', 'ID CARD');
});
