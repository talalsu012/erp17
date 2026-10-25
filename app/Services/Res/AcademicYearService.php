<?php

namespace App\Services\Res;

use App\Models\Reg\Level;

/**
 * تجميع مستويات السنة الأكاديمية الواحدة (فصلان أو أكثر) في النظام الفصلي-السنوي.
 * تُجمَّع المستويات التي لها المستوى الأب (reg_levels.parent_code) نفسه؛ ومن بلا أب سنته مستواه وحده.
 */
class AcademicYearService
{
    /**
     * أكواد مستويات السنة التي ينتمي إليها المستوى (تشمل المستوى نفسه).
     *
     * @return list<int>
     */
    public function levelsOfYear(int|string $level): array
    {
        $model = Level::query()->find($level);

        if ($model === null || blank($model->parent_code)) {
            return [(int) $level];
        }

        return Level::query()
            ->where('parent_code', $model->parent_code)
            ->pluck('code')
            ->map(fn ($code): int => (int) $code)
            ->all();
    }
}
