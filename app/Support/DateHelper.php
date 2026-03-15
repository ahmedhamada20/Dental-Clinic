<?php

namespace App\Support;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Get human readable date format
     */
    public static function format(Carbon|string|null $date, string $format = 'Y-m-d'): string|null
    {
        if (! $date) {
            return null;
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format($format);
    }

    /**
     * Get human readable datetime format
     */
    public static function formatDateTime(Carbon|string|null $date, string $format = 'Y-m-d H:i'): string|null
    {
        if (! $date) {
            return null;
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format($format);
    }

    /**
     * Get relative time format (e.g., "2 hours ago")
     */
    public static function diffForHumans(Carbon|string|null $date): string|null
    {
        if (! $date) {
            return null;
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->diffForHumans();
    }

    /**
     * Check if date is in the past
     */
    public static function isPast(Carbon|string $date): bool
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->isPast();
    }

    /**
     * Check if date is in the future
     */
    public static function isFuture(Carbon|string $date): bool
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->isFuture();
    }

    /**
     * Check if date is today
     */
    public static function isToday(Carbon|string $date): bool
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->isToday();
    }

    /**
     * Get start of day
     */
    public static function startOfDay(Carbon|string $date): Carbon
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->startOfDay();
    }

    /**
     * Get end of day
     */
    public static function endOfDay(Carbon|string $date): Carbon
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->endOfDay();
    }

    /**
     * Get days between two dates
     */
    public static function daysBetween(Carbon|string $from, Carbon|string $to): int
    {
        if (is_string($from)) {
            $from = Carbon::parse($from);
        }

        if (is_string($to)) {
            $to = Carbon::parse($to);
        }

        return $from->diffInDays($to);
    }

    /**
     * Check if date is between two dates
     */
    public static function isBetween(Carbon|string $date, Carbon|string $start, Carbon|string $end): bool
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        if (is_string($start)) {
            $start = Carbon::parse($start);
        }

        if (is_string($end)) {
            $end = Carbon::parse($end);
        }

        return $date->between($start, $end);
    }

    /**
     * Get current date
     */
    public static function now(): Carbon
    {
        return Carbon::now();
    }

    /**
     * Get current date only (no time)
     */
    public static function today(): Carbon
    {
        return Carbon::today();
    }

    /**
     * Add days to date
     */
    public static function addDays(Carbon|string $date, int $days): Carbon
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->addDays($days);
    }

    /**
     * Subtract days from date
     */
    public static function subtractDays(Carbon|string $date, int $days): Carbon
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->subDays($days);
    }
}

