<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'full_name'     => $this->full_name,
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'id_type'       => $this->id_type,
            'id_type_display'=> $this->id_type_display,
            'id_number'     => $this->id_number,
            'nationality'   => $this->nationality,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'address'       => $this->address,
            'total_bookings'=> $this->when(
                $this->relationLoaded('bookings'),
                fn() => $this->bookings->count()
            ),
        ];
    }
}