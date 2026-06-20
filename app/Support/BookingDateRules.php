<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Validation\Validator;

class BookingDateRules
{
    public const MIN_NIGHTS = 1;

    public const MAX_NIGHTS = 365;

    public static function rules(bool $checkinMustBeTodayOrFuture = true): array
    {
        $checkin = ['required', 'date'];

        if ($checkinMustBeTodayOrFuture) {
            $checkin[] = 'after_or_equal:today';
        }

        return [
            'checkin_date'  => $checkin,
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
        ];
    }

    public static function searchRules(): array
    {
        return [
            'checkin_date'  => ['nullable', 'date', 'after_or_equal:today'],
            'checkout_date' => ['nullable', 'date', 'after:checkin_date'],
        ];
    }

    public static function messages(): array
    {
        return [
            'checkin_date.after_or_equal' => 'Check-in date cannot be in the past.',
            'checkout_date.after'         => 'Check-out must be after check-in.',
        ];
    }

    public static function nights(string $checkin, string $checkout): int
    {
        return Carbon::parse($checkin)->startOfDay()
            ->diffInDays(Carbon::parse($checkout)->startOfDay());
    }

    public static function validateStayLength(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $checkin  = $validator->getValue('checkin_date');
        $checkout = $validator->getValue('checkout_date');

        if (!$checkin || !$checkout) {
            return;
        }

        $nights = self::nights($checkin, $checkout);

        if ($nights < self::MIN_NIGHTS) {
            $validator->errors()->add(
                'checkout_date',
                'Stay must be at least ' . self::MIN_NIGHTS . ' night.'
            );
        }

        if ($nights > self::MAX_NIGHTS) {
            $validator->errors()->add(
                'checkout_date',
                'Stay cannot exceed ' . self::MAX_NIGHTS . ' nights.'
            );
        }
    }

    public static function assertValidStay(string $checkin, string $checkout): void
    {
        $nights = self::nights($checkin, $checkout);

        if ($nights < self::MIN_NIGHTS) {
            throw new \InvalidArgumentException(
                'Checkout must be at least one night after check-in.'
            );
        }

        if ($nights > self::MAX_NIGHTS) {
            throw new \InvalidArgumentException(
                'Stay cannot exceed ' . self::MAX_NIGHTS . ' nights.'
            );
        }
    }
}
