<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'description', 'quantity', 'unit_price', 'total', 'type',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTypeDisplayAttribute(): string
    {
        return match ($this->type) {
            'room_charge' => 'Room Charge',
            'service'     => 'Service',
            'food'        => 'Food & Beverage',
            'minibar'     => 'Minibar',
            'laundry'     => 'Laundry',
            'other'       => 'Other',
            default       => ucfirst($this->type),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'room_charge' => 'blue',
            'service'     => 'purple',
            'food'        => 'orange',
            'minibar'     => 'amber',
            'laundry'     => 'teal',
            'other'       => 'slate',
            default       => 'slate',
        };
    }
}