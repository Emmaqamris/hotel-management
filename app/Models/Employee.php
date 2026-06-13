<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'hotel_id', 'name', 'email', 'password', 'role',
        'phone', 'avatar', 'hire_date', 'salary', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
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
        return $this->hasMany(HousekeepingLog::class, 'assigned_to');
    }

    // Role helpers
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isManager(): bool  { return $this->role === 'manager'; }
    public function isReceptionist(): bool { return $this->role === 'receptionist'; }
    public function isHousekeeper(): bool  { return $this->role === 'housekeeper'; }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }
}