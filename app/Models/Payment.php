<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'hotel_id', 'amount', 'method', 'status',
        'reference_number', 'transaction_id', 'paid_at', 'notes', 'processed_by',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'processed_by');
    }

    public function getMethodDisplayAttribute(): string
    {
        return match ($this->method) {
            'cash'          => 'Cash',
            'credit_card'   => 'Credit Card',
            'debit_card'    => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'check'         => 'Check',
            default         => ucfirst(str_replace('_', ' ', $this->method)),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'emerald',
            'pending'   => 'yellow',
            'failed'    => 'red',
            'refunded'  => 'orange',
            default     => 'slate',
        };
    }
}