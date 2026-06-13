<?php

use Illuminate\Support\Facades\Schedule;

// Send check-in reminder emails every day at 10:00 AM
Schedule::command('hotel:send-checkin-reminders')->dailyAt('10:00');

// Clear processed queue jobs every week
Schedule::command('queue:prune-batches --hours=48')->weekly();