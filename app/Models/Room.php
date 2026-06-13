<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'number',
        'type',
        'status',
        'floor',
        'capacity',
        'price_per_night',
        'description',
        'amenities',
        'image',
        'is_active',
    ];

    protected $casts = [
        'amenities'       => 'array',
        'price_per_night' => 'decimal:2',
        'is_active'       => 'boolean',
        'floor'           => 'integer',
        'capacity'        => 'integer',
    ];

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function housekeepingLogs(): HasMany
    {
        return $this->hasMany(HousekeepingLog::class);
    }

    // ─────────────────────────────────────────
    // Query Scopes
    // ─────────────────────────────────────────

    /** Only rooms that are available for booking */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    /** Filter by room type */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /** Filter to a specific hotel */
    public function scopeForHotel(Builder $query, int $hotelId): Builder
    {
        return $query->where('hotel_id', $hotelId);
    }

    /** Only active rooms */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ─────────────────────────────────────────
// Computed / Display Helpers
// ─────────────────────────────────────────

/** Human-readable room type label */
public function getTypeDisplayAttribute(): string
{
    return match ($this->type) {
        'standard'       => 'Standard',
        'deluxe'         => 'Deluxe',
        'family_suite'   => 'Family Suite',
        'business_suite' => 'Business Suite',
        default          => ucfirst($this->type),
    };
}

/** Tailwind color name for the status badge */
public function getStatusColorAttribute(): string
{
    return match ($this->status) {
        'available'   => 'emerald',
        'maintenance' => 'slate',
        default       => 'slate',
    };
}

/** Human-readable status label */
public function getStatusDisplayAttribute(): string
{
    return match ($this->status) {
        'available'   => 'Available',
        'maintenance' => 'Maintenance',
        default       => ucfirst($this->status),
    };
}

/** URL for the room image (falls back to placeholder) */
public function getImageUrlAttribute(): string
{
    return $this->image
        ? asset('storage/' . $this->image)
        : asset('images/room-placeholder.png');
}

/** Whether the room can currently be booked */
public function isAvailableForBooking(): bool
{
    return $this->status === 'available'
        && $this->is_active;
}

/**
 * Alias used by BookingService
 */
public function isBookable(): bool
{
    return $this->isAvailableForBooking();
}

/** Active booking for this room right now (if any) */
public function getCurrentBookingAttribute(): ?Booking
{
    return $this->bookings()
        ->whereIn('status', ['confirmed', 'checked_in'])
        ->whereDate('checkin_date', '<=', now())
        ->whereDate('checkout_date', '>=', now())
        ->with('guest')
        ->first();
}
    }