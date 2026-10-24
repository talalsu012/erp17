<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amount = collect([$this->pay1, $this->pay2, $this->pay3, $this->pay4])->first(fn ($v) => $v > 0) ?? 0;

        return [
            'UNIVERSITYNO' => $this->university_no,
            'NAME' => $this->name_ar,
            'AMOUNT' => $amount,
            'FACULTY' => $this->faculty->name_ar ?? null,
            'SYS-TYPE' => 'REG',
            'SYS-TYPE-CODE' => 4000,
        ];
    }
}
