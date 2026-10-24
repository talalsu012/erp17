<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'UNIVERSITYNO' => $this->university_no,
            'NAME' => $this->name_ar,
            'AMOUNT' => $this->amount,
            'FACULTY' => $this->faculty->name_ar ?? null,
            'SYS-TYPE' => $this->systype->name_ar ?? null,
            'SYS-TYPE-CODE' => $this->sys_type,
        ];
    }
}
