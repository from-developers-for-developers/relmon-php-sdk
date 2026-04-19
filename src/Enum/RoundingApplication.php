<?php

namespace FromDevelopersForDevelopers\RelMon\Enum;

class RoundingApplication
{
    public const TAX = 'tax';
    public const TOTAL = 'total';

    public static function values(): array
    {
        return [
            self::TAX,
            self::TOTAL,
        ];
    }

    public static function tryFrom(mixed $value): ?string
    {
        return in_array($value, self::values(), true) ? $value : null;
    }
}
