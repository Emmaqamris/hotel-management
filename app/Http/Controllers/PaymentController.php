<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    // ─────────────────────────────────────────
    // PAYMENT FORM  /invoices/{invoice}/pay
    // ─────────────────────────────────────────

    public function create(Request $request, Invoice $invoice): RedirectResponse|View
    {
        $this->authorizeInvoice($request, $invoice);

        if ($invoice->isPaid()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'This invoice has already been paid.');
        }

        $invoice->load(['hotel', 'booking.room', 'guest', 'items']);

        return view('payments.create', compact('invoice'));
    }

    // ─────────────────────────────────────────
    // PROCESS PAYMENT  POST /invoices/{invoice}/pay
    // ─────────────────────────────────────────

    public function store(ProcessPaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);

        try {
            $payment = $this->paymentService->processPayment(
                $invoice,
                $request->validated(),
                $request->user('employee')->id
            );

            return redirect()
                ->route('payments.receipt', $payment)
                ->with('success', 'Payment processed successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // RECEIPT  /payments/{payment}/receipt
    // ─────────────────────────────────────────

    public function receipt(Request $request, Payment $payment): View
    {
        if ($request->user('employee')->hotel_id !== $payment->hotel_id) {
            abort(403);
        }

        $payment->load([
            'invoice.hotel',
            'invoice.booking.room',
            'invoice.guest',
            'invoice.items',
            'processedBy',
        ]);

        return view('payments.receipt', compact('payment'));
    }

    // ─────────────────────────────────────────
    // Private
    // ─────────────────────────────────────────

    private function authorizeInvoice(Request $request, Invoice $invoice): void
    {
        if ($request->user('employee')->hotel_id !== $invoice->hotel_id) {
            abort(403, 'You do not have access to this invoice.');
        }
    }
}