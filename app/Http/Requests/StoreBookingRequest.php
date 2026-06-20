<?php

namespace App\Http\Requests;

use App\Support\BookingDateRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(BookingDateRules::rules(), [
            'room_id'          => ['required', 'exists:rooms,id'],
            'guest_id'         => ['required', 'exists:guests,id'],
            'adults'           => ['required', 'integer', 'min:1', 'max:10'],
            'children'         => ['nullable', 'integer', 'min:0'],
            'special_requests' => ['nullable', 'string', 'max:500'],
            'source'           => ['nullable', 'in:walk_in,phone,online,ota'],
        ]);
    }

    public function messages(): array
    {
        return BookingDateRules::messages();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            BookingDateRules::validateStayLength($validator);
        });
    }
}
