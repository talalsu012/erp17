<?php

namespace App\Services\Card;

use App\Models\Reg\Card;
use App\Reg\CardStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * التحقق من بطاقة الطالب عند مسح رمز QR: رابط موقَّع لا يمكن تزويره، والحكم يُحسب لحظة المسح
 * من حالة البطاقة وتاريخ انتهائها، فتُرفض الموقوفة والمنتهية حتى لو كانت مطبوعة سابقاً.
 */
class CardVerificationService
{
    public static function url(Card $card): string
    {
        return URL::signedRoute('cards.verify', ['card' => $card->getKey()]);
    }

    /**
     * @return array{allowed: bool, reason: ?string}
     */
    public function evaluate(Card $card): array
    {
        if ($card->trashed()) {
            return ['allowed' => false, 'reason' => 'البطاقة ملغاة'];
        }

        if ($card->status !== CardStatus::active) {
            return ['allowed' => false, 'reason' => 'البطاقة غير سارية: '.($card->status?->getLabel() ?? 'غير محددة')];
        }

        if (! $card->student) {
            return ['allowed' => false, 'reason' => 'لا يوجد طالب مرتبط بالبطاقة'];
        }

        if ($card->expiry_date && Carbon::parse($card->expiry_date)->endOfDay()->isPast()) {
            return ['allowed' => false, 'reason' => 'انتهت صلاحية البطاقة'];
        }

        return ['allowed' => true, 'reason' => null];
    }
}
