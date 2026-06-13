<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'invoice_number'  => $this->invoice_number,
            'status'          => $this->status,
            'status_display'  => $this->status_display,
            'subtotal'        => (float) $this->subtotal,
            'tax_rate'        => (float) $this->tax_rate,
            'tax_amount'      => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total'           => (float) $this->total,
            'amount_due'      => (float) $this->amount_due,
            'is_paid'         => $this->isPaid(),
            'can_be_paid'     => $this->canBePaid(),
            'issued_at'       => $this->issued_at?->toISOString(),
            'due_at'          => $this->due_at?->toISOString(),
            'items'           => InvoiceItemResource::collection(
                $this->whenLoaded('items')
            ),
            'payment'         => new PaymentResource($this->whenLoaded('payment')),
        ];
    }
}