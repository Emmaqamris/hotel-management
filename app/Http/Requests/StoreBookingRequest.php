<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'          => ['required', 'exists:rooms,id'],
            'guest_id'         => ['required', 'exists:guests,id'],
            'checkin_date'     => ['required', 'date', 'after_or_equal:today'],
            'checkout_date'    => ['required', 'date', 'after:checkin_date'],
            'adults'           => ['required', 'integer', 'min:1', 'max:10'],
            'children'         => ['nullable', 'integer', 'min:0'],
            'special_requests' => ['nullable', 'string', 'max:500'],
            'source'           => ['nullable', 'in:walk_in,phone,online,ota'],
        ];
    }
}