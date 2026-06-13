<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'booking_number'   => $this->booking_number,
            'status'           => $this->status,
            'status_display'   => $this->status_display,
            'checkin_date'     => $this->checkin_date->format('Y-m-d'),
            'checkout_date'    => $this->checkout_date->format('Y-m-d'),
            'actual_checkin'   => $this->actual_checkin?->toISOString(),
            'actual_checkout'  => $this->actual_checkout?->toISOString(),
            'nights'           => $this->nights,
            'adults'           => $this->adults,
            'children'         => $this->children,
            'room_rate'        => (float) $this->room_rate,
            'total_amount'     => (float) $this->total_amount,
            'special_requests' => $this->special_requests,
            'source'           => $this->source,
            'can_check_in'     => $this->canCheckIn(),
            'can_check_out'    => $this->canCheckOut(),
            'can_cancel'       => $this->canCancel(),
            'room'             => new RoomResource($this->whenLoaded('room')),
            'guest'            => new GuestResource($this->whenLoaded('guest')),
            'invoice'          => new InvoiceResource($this->whenLoaded('invoice')),
            'created_at'       => $this->created_at->toISOString(),
        ];
    }
}