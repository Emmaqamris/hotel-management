<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('employee')->hasRole(['admin', 'manager']);
    }
    public function rules(): array
    {
        $roomId = $this->route('room')?->id;
        return [
            'number' => [
                'required', 'string', 'max:10',
                Rule::unique('rooms', 'number')
                    ->where('hotel_id', $this->user('employee')->hotel_id)
                    ->ignore($roomId),
            ],
            'type'           => ['required', Rule::in(['standard', 'deluxe', 'family_suite', 'business_suite'])],
            'floor'          => ['required', 'integer', 'min:1', 'max:100'],
            'capacity'       => ['required', 'integer', 'min:1', 'max:10'],
            'price_per_night'=> ['required', 'numeric', 'min:1', 'max:99999'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'amenities'      => ['nullable', 'array'],
            'amenities.*'    => ['string', 'max:50'],
            'image'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'status'         => ['nullable', Rule::in(['available', 'maintenance'])],
            'is_active'      => ['nullable', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return [
            'number.unique'       => 'A room with this number already exists in your hotel.',
            'price_per_night.min' => 'Price must be at least 1.',
            'image.max'           => 'Image must be smaller than 2MB.',
        ];
    }
}
