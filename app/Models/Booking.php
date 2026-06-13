<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'hotel_id',
        'room_id',
        'guest_id',
        'employee_id',
        'checkin_date',
        'checkout_date',
        'actual_checkin',
        'actual_checkout',
        'status',
        'adults',
        'children',
        'room_rate',
        'total_amount',
        'special_requests',
        'source',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'checkin_date'    => 'date',
        'checkout_date'   => 'date',
        'actual_checkin'  => 'datetime',
        'actual_checkout' => 'datetime',
        'cancelled_at'    => 'datetime',
        'room_rate'       => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'adults'          => 'integer',
        'children'        => 'integer',
    ];

    // ─────────────────────────────────────────
    // Boot: auto-generate booking number
    // ─────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Booking $booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = self::generateBookingNumber();
            }
        });
    }

    public static function generateBookingNumber(): string
    {
        do {
            // e.g. BK-A1B2C3D4
            $number = 'BK-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('booking_number', $number)->exists());

        return $number;
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeForHotel(Builder $query, int $hotelId): Builder
    {
        return $query->where('hotel_id', $hotelId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['confirmed', 'checked_in']);
    }

    public function scopeCheckinToday(Builder $query): Builder
    {
        return $query->where('status', 'confirmed')
                     ->whereDate('checkin_date', today());
    }

    public function scopeCheckoutToday(Builder $query): Builder
    {
        return $query->where('status', 'checked_in')
                     ->whereDate('checkout_date', today());
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('booking_number', 'like', "%{$term}%")
              ->orWhereHas('guest', fn($gq) =>
                  $gq->where('first_name', 'like', "%{$term}%")
                     ->orWhere('last_name',  'like', "%{$term}%")
                     ->orWhere('phone',      'like', "%{$term}%")
              )
              ->orWhereHas('room', fn($rq) =>
                  $rq->where('number', 'like', "%{$term}%")
              );
        });
    }

    // ─────────────────────────────────────────
    // Computed Attributes
    // ─────────────────────────────────────────

    /** Convenience alias for the scheduled check-in date */
    #[Attribute]
    public function checkIn(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->checkin_date ? Carbon::parse($this->checkin_date) : null,
        );
    }

    /** Convenience alias for the scheduled check-out date */
    #[Attribute]
    public function checkOut(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->checkout_date ? Carbon::parse($this->checkout_date) : null,
        );
    }

    /** Number of nights between checkin and checkout */
    #[Attribute]
    public function nights(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->checkin_date && $this->checkout_date
                ? max(0, $this->checkin_date->diffInDays($this->checkout_date))
                : 0,
        );
    }

    /** Tailwind colour for the status badge */
    #[Attribute]
    public function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                'pending'     => 'yellow',
                'confirmed'   => 'blue',
                'checked_in'  => 'green',
                'checked_out' => 'slate',
                'cancelled'   => 'red',
                'no_show'     => 'orange',
                default       => 'slate',
            },
        );
    }

    /** Human-readable status */
    #[Attribute]
    public function statusDisplay(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                'pending'     => 'Pending',
                'confirmed'   => 'Confirmed',
                'checked_in'  => 'Checked In',
                'checked_out' => 'Checked Out',
                'cancelled'   => 'Cancelled',
                'no_show'     => 'No Show',
                default       => ucfirst($this->status),
            },
        );
    }

    // ─────────────────────────────────────────
    // State Machine Guards
    // ─────────────────────────────────────────

    public function canCheckIn(): bool
    {
        return $this->status === 'confirmed'
            && $this->checkin_date->lte(today());
    }

    public function canCheckOut(): bool
    {
        return $this->status === 'checked_in';
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canMarkNoShow(): bool
    {
        return $this->status === 'confirmed'
            && $this->checkin_date->lt(today());
    }
}