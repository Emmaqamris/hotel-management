<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    // ─────────────────────────────────────────
    // LIST  /invoices
    // ─────────────────────────────────────────

    public function index(Request $request): View
    {
        $hotelId = $request->user('employee')->hotel_id;
        $status  = $request->get('status', '');
        $search  = $request->get('search', '');

        $invoices = Invoice::where('hotel_id', $hotelId)
            ->with(['booking.room', 'guest', 'payment'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', fn($gq) =>
                      $gq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name',  'like', "%{$search}%")
                  );
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Status totals for the summary bar
        $summary = Invoice::where('hotel_id', $hotelId)
            ->selectRaw('status, COUNT(*) as count, SUM(total) as total_amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('invoices.index', compact('invoices', 'summary', 'status', 'search'));
    }

    // ─────────────────────────────────────────
    // SHOW  /invoices/{invoice}
    // ─────────────────────────────────────────

    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorizeInvoice($request, $invoice);

        $invoice->load([
            'hotel',
            'booking.room',
            'booking.employee',
            'guest',
            'items',
            'payment.processedBy',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    // ─────────────────────────────────────────
    // ADD EXTRA CHARGE
    // ─────────────────────────────────────────

    public function addCharge(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:200'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:100'],
            'unit_price'  => ['required', 'numeric', 'min:0.01', 'max:99999'],
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
            return back()->with('success', 'Charge added to the invoice.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // REMOVE CHARGE
    // ─────────────────────────────────────────

    public function removeCharge(
        Request     $request,
        Invoice     $invoice,
        InvoiceItem $item
    ): RedirectResponse {
        $this->authorizeInvoice($request, $invoice);

        // Verify item belongs to this invoice
        if ((int) $item->invoice_id !== $invoice->id) {
            abort(404);
        }

        try {
            $this->invoiceService->removeItem($invoice, $item);
            return back()->with('success', 'Charge removed from the invoice.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // APPLY DISCOUNT
    // ─────────────────────────────────────────

    public function applyDiscount(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);

        $data = $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->invoiceService->applyDiscount($invoice, (float) $data['discount_amount']);
            return back()->with('success', 'Discount applied successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // PRINT  /invoices/{invoice}/print
    // Opens a clean printable page in a new tab
    // ─────────────────────────────────────────

    public function print(Request $request, Invoice $invoice): View
    {
        $this->authorizeInvoice($request, $invoice);

        $invoice->load([
            'hotel',
            'booking.room',
            'guest',
            'items',
            'payment.processedBy',
        ]);

        return view('invoices.print', compact('invoice'));
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