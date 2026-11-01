<?php

namespace App\Http\Controllers;

use App\Models\Reg\Card;
use App\Services\Card\CardVerificationService;
use Illuminate\Contracts\View\View;

/**
 * صفحة التحقق التي يفتحها الحرس بمسح رمز البطاقة: بلا تسجيل دخول، ولا تعرض الرقم الجامعي ولا بيانات حساسة.
 */
class CardVerificationController extends Controller
{
    public function __invoke(int $card, CardVerificationService $verification): View
    {
        $card = Card::withTrashed()->with(['student.faculty', 'student.level', 'student.batch'])->findOrFail($card);
        $student = $card->student;

        return view('cards.verify', [
            'verdict' => $verification->evaluate($card),
            'name' => $student?->name_ar,
            'photo' => $student?->photoDataUri(),
            'faculty' => $student?->faculty?->name_ar,
            'level' => $student?->level?->name_ar,
            'batch' => $student?->batch?->name_ar,
            'status' => $card->status?->getLabel(),
            'expiry' => $card->expiry_date ? \Illuminate\Support\Carbon::parse($card->expiry_date)->format('Y-m-d') : null,
        ]);
    }
}
