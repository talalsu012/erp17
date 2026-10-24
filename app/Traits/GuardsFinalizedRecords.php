<?php

namespace App\Traits;

use DomainException;
use Illuminate\Database\Eloquent\Model;

/**
 * يمنع تعديل أو حذف صف مقفل (معتمد) إلا من المدير الأعلى.
 * القفل نفسه يُضبط باستعلام جماعي عند الاعتماد، فلا يصطدم بهذا الحارس.
 *
 * @mixin Model
 */
trait GuardsFinalizedRecords
{
    protected static function bootGuardsFinalizedRecords(): void
    {
        static::updating(fn (Model $model) => static::assertNotFinalized($model));
        static::deleting(fn (Model $model) => static::assertNotFinalized($model));
    }

    protected static function assertNotFinalized(Model $model): void
    {
        if (! $model->getOriginal('is_locked')) {
            return;
        }

        if (auth()->user()?->isSuperAdmin()) {
            return;
        }

        throw new DomainException('هذه النتيجة معتمدة ومقفلة نهائياً، ولا يعدّلها إلا المدير الأعلى.');
    }
}
