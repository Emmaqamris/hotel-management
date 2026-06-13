<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Traits\ApiResponds;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    use ApiResponds;

    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    // POST /api/invoices/{invoice}/pay
    public function store(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        if (!$request->user()->hasRole(['admin', 'manager', 'receptionist'])) {
            return $this->forbidden('Insufficient permissions to process payments.');
        }

        $data = $request->validate([
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'method'           => ['required', Rule::in(['cash','credit_card','debit_card','bank_transfer','check'])],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'transaction_id'   => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payment = $this->paymentService->processPayment(
                $invoice,
                $data,
                $request->user()->id
            );

            return $this->created(
                new PaymentResource($payment),
                'Payment processed successfully'
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}