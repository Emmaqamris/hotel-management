<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'amount'           => (float) $this->amount,
            'method'           => $this->method,
            'method_display'   => $this->method_display,
            'status'           => $this->status,
            'reference_number' => $this->reference_number,
            'paid_at'          => $this->paid_at?->toISOString(),
            'notes'            => $this->notes,
            'processed_by'     => $this->processedBy?->name,
        ];
    }
}