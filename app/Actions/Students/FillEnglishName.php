<?php

namespace App\Actions\Students;

use App\Support\EnglishNameTranslator;

/**
 * يملأ الاسم الإنجليزي من الاسم العربي عند الإدخال أو التعديل إذا كان الاسم الإنجليزي فارغاً.
 */
class FillEnglishName
{
    public function __construct(private readonly EnglishNameTranslator $translator) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(array $data): array
    {
        if (blank($data['name_en'] ?? null) && filled($data['name_ar'] ?? null)) {
            $data['name_en'] = $this->translator->translate((string) $data['name_ar']);
        }

        return $data;
    }
}
