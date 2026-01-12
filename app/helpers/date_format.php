<?php

declare(strict_types=1);

/**
 * Format a date string as Y-m-d.
 * Returns '-' when empty and falls back to the raw value on parse errors.
 */
function formatDateOnly(?string $value): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    try {
        return (new DateTimeImmutable($value))->format('Y-m-d');
    } catch (\Exception) {
        return $value;
    }
}
