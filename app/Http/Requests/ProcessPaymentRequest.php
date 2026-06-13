<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('employee')
            ->hasRole(['admin', 'manager', 'receptionist']);
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999',
            ],
            'method' => [
                'required',
                Rule::in(['cash', 'credit_card', 'debit_card', 'bank_transfer', 'check']),
            ],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'transaction_id'   => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min'  => 'Payment amount must be greater than zero.',
            'method.in'   => 'Please select a valid payment method.',
            'amount.max'  => 'Payment amount is too large.',
        ];
    }
}