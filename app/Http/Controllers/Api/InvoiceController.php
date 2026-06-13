<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Http\Traits\ApiResponds;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponds;

    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    // GET /api/invoices/{invoice}
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $invoice->load(['booking.room', 'guest', 'items', 'payment']);

        return $this->ok(new InvoiceResource($invoice));
    }

    // POST /api/invoices/{invoice}/charges
    public function addCharge(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'description' => ['required', 'string', 'max:200'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:100'],
            'unit_price'  => ['required', 'numeric', 'min:0.01'],
            'type'        => ['required', 'in:service,food,minibar,laundry,other'],
        ]);

        try {
            $this->invoiceService->addExtraCharge(
                $invoice,
                $data['description'],
                (float) $data['unit_price'],
                (int)   $data['quantity'],
                $data['type']
            );

            $invoice->refresh()->load('items');

            return $this->ok(new InvoiceResource($invoice), 'Charge added successfully');

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    // POST /api/invoices/{invoice}/discount
    public function applyDiscount(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->hotel_id !== $request->user()->hotel_id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->invoiceService->applyDiscount($invoice, (float) $data['discount_amount']);
            $invoice->refresh()->load('items');

            return $this->ok(new InvoiceResource($invoice), 'Discount applied');

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}