<?php

namespace App\Models\Res;

use App\Models\Reg\LtColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Grade extends Model
{
    use SoftDeletes;

    /** خطة البكالريوس: تُستخدم عند خلو بيانات الطالب من الخطة */
    public const DEFAULT_PLAN = '20060001';

    private const CACHE_SECONDS = 3600;

    protected $table = 'res_grades';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
        ];
    }

    protected static function booted(): void
    {
        $flush = static function (Grade $grade): void {
            Cache::forget(self::cacheKey((string) $grade->plan));
            Cache::forget(self::cacheKey((string) $grade->getOriginal('plan')));
        };

        static::saved($flush);
        static::deleted($flush);
        static::restored($flush);
    }

    public function plans(): BelongsTo
    {
        return $this->belongsTo(LtColumn::class, 'plan');
    }

    /**
     * جدول تقديرات الخطة مرتباً من الأعلى (مخزّن مؤقتاً ساعة، ويُمسح عند أي تعديل).
     *
     * @return Collection<int, Grade>
     */
    public static function forPlan(string $plan): Collection
    {
        return Cache::remember(
            self::cacheKey($plan),
            self::CACHE_SECONDS,
            fn () => static::query()->where('plan', $plan)->orderByDesc('upper')->get(),
        );
    }

    /** التقدير المطابق لمجموع الدرجات في خطة الطالب (null إن لم يوجد نطاق مطابق) */
    public static function forMark(int|float $mark, string $plan = self::DEFAULT_PLAN): ?Grade
    {
        return self::forPlan($plan)->first(
            fn (Grade $grade): bool => $mark >= $grade->lower && $mark <= $grade->upper,
        );
    }

    /** أدنى تقدير ناجح في الخطة (نقاطه > 0)، ويحدد حد النجاح للخطة */
    public static function lowestPassing(string $plan = self::DEFAULT_PLAN): ?Grade
    {
        return self::forPlan($plan)
            ->filter(fn (Grade $grade): bool => (float) $grade->point > 0)
            ->sortBy('lower')
            ->first();
    }

    private static function cacheKey(string $plan): string
    {
        return 'res_grades:plan:'.$plan;
    }
}
