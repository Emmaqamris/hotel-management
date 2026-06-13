<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'id_type',
        'id_number',
        'nationality',
        'date_of_birth',
        'address',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
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

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeForHotel(Builder $query, int $hotelId): Builder
    {
        return $query->where('hotel_id', $hotelId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name',  'like', "%{$term}%")
              ->orWhere('email',      'like', "%{$term}%")
              ->orWhere('phone',      'like', "%{$term}%")
              ->orWhere('id_number',  'like', "%{$term}%");
        });
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getIdTypeDisplayAttribute(): string
    {
        return match ($this->id_type) {
            'passport'         => 'Passport',
            'national_id'      => 'National ID',
            'drivers_license'  => "Driver's License",
            default            => ucfirst($this->id_type),
        };
    }

    /** Most recent active booking */
    public function getActiveBookingAttribute(): ?Booking
    {
        return $this->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->latest()
            ->first();
    }

    public function getTotalBookingsAttribute(): int
    {
        return $this->bookings()->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) $this->bookings()
            ->whereIn('status', ['checked_out', 'checked_in'])
            ->sum('total_amount');
    }
}