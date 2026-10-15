<?php

namespace App\Support;

/** Philippine mobile numbers: typed as 09171234567, saved and shown as 0917 123 4567. */
class Phone
{
    public const MESSAGE = 'Enter a mobile number like 09171234567 (11 digits, starting with 09).';

    /** Removes spaces and dashes. Returns null when nothing is left. */
    public static function digits(?string $value): ?string
    {
        $d = preg_replace('/[\s-]/', '', (string) $value);

        return $d === '' ? null : $d;
    }

    /** True for 11 digits starting with 09. */
    public static function isMobile(?string $value): bool
    {
        return (bool) preg_match('/^09\d{9}$/', (string) self::digits($value));
    }

    /** "09171234567" becomes "0917 123 4567". Returns null for an empty value. */
    public static function formatMobile(?string $value): ?string
    {
        $d = self::digits($value);

        return $d === null ? null : substr($d, 0, 4).' '.substr($d, 4, 3).' '.substr($d, 7, 4);
    }

    /** A validation rule that accepts only a mobile number. Use it with 'nullable'. */
    public static function rule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! self::isMobile($value)) {
                $fail(self::MESSAGE);
            }
        };
    }
}