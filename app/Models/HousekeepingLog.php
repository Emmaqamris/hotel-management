<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousekeepingLog extends Model
{
    use HasFactory;

    protected $table = 'housekeeping_logs';

    protected $fillable = [
        'hotel_id', 'room_id', 'assigned_to', 'booking_id',
        'type', 'status', 'priority', 'scheduled_at',
        'started_at', 'completed_at', 'notes', 'issues_found',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function hotel(): BelongsTo    { return $this->belongsTo(Hotel::class); }
    public function room(): BelongsTo     { return $this->belongsTo(Room::class); }
    public function booking(): BelongsTo  { return $this->belongsTo(Booking::class); }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'scheduled'   => 'info',
            'in_progress' => 'warning',
            'completed'   => 'success',
            'skipped'     => 'secondary',
            default       => 'secondary',
        };
    }
}