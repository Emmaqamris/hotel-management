<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'booking_id', 'hotel_id', 'guest_id',
        'subtotal', 'tax_rate', 'tax_amount', 'discount_amount',
        'extra_charges', 'total', 'status', 'issued_at', 'due_at', 'notes',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'extra_charges'   => 'decimal:2',
        'total'           => 'decimal:2',
        'issued_at'       => 'datetime',
        'due_at'          => 'datetime',
    ];

    // ─────────────────────────────────────────
    // Boot — auto invoice number
    // ─────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $year  = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'INV-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function hotel(): BelongsTo   { return $this->belongsTo(Hotel::class); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function guest(): BelongsTo   { return $this->belongsTo(Guest::class); }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('created_at');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeForHotel(Builder $query, int $hotelId): Builder
    {
        return $query->where('hotel_id', $hotelId);
    }

    // ─────────────────────────────────────────
    // Status helpers
    // ─────────────────────────────────────────

    public function isPaid(): bool      { return $this->status === 'paid'; }
    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isIssued(): bool    { return $this->status === 'issued'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'issued']);
    }

    public function canBePaid(): bool
    {
        return in_array($this->status, ['draft', 'issued']);
    }

    // ─────────────────────────────────────────
    // Display helpers
    // ─────────────────────────────────────────

    public function getAmountDueAttribute(): float
    {
        if ($this->isPaid()) return 0.0;
        return max(0.0, (float) $this->total);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'slate',
            'issued'    => 'blue',
            'paid'      => 'emerald',
            'cancelled' => 'red',
            default     => 'slate',
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'issued'    => 'Issued',
            'paid'      => 'Paid',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function getRoomChargeTotalAttribute(): float
    {
        return (float) $this->items->where('type', 'room_charge')->sum('total');
    }

    public function getExtraChargeTotalAttribute(): float
    {
        return (float) $this->items->where('type', '!=', 'room_charge')->sum('total');
    }
}