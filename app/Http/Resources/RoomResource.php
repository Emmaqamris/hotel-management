<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'number'          => $this->number,
            'type'            => $this->type,
            'type_display'    => $this->type_display,
            'status'          => $this->status,
            'status_display'  => $this->status_display,
            'floor'           => $this->floor,
            'capacity'        => $this->capacity,
            'price_per_night' => (float) $this->price_per_night,
            'description'     => $this->description,
            'amenities'       => $this->amenities ?? [],
            'image_url'       => $this->image_url,
            'is_active'       => $this->is_active,
            'is_bookable'     => $this->isBookable(),
        ];
    }
}