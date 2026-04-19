<?php

namespace FromDevelopersForDevelopers\RelMon\Enum;

class RoundingMode
{
    public const HALF_AWAY_FROM_ZERO = 'haway';
    public const HALF_TOWARDS_ZERO = 'hzero';
    public const HALF_EVEN = 'heven';
    public const UP = 'up';
    public const DOWN = 'down';

    public static function values(): array
    {
        return [
            self::HALF_AWAY_FROM_ZERO,
            self::HALF_TOWARDS_ZERO,
            self::HALF_EVEN,
            self::UP,
            self::DOWN,
        ];
    }

    public static function tryFrom(mixed $value): ?string
    {
        return in_array($value, self::values(), true) ? $value : null;
    }

    public static function round(string $mode, int|float $valueInMinors, int $precision = 0): int
    {
        return match ($mode) {
            self::HALF_AWAY_FROM_ZERO => (int)round($valueInMinors, $precision, PHP_ROUND_HALF_UP),
            self::HALF_TOWARDS_ZERO => (int)round($valueInMinors, $precision, PHP_ROUND_HALF_DOWN),
            self::HALF_EVEN => (int)round($valueInMinors, $precision, PHP_ROUND_HALF_EVEN),
            self::UP => (int)($valueInMinors >= 0 ? ceil($valueInMinors) : floor($valueInMinors)),
            self::DOWN => (int)$valueInMinors,
            default => throw new \InvalidArgumentException("Invalid rounding mode: $mode"),
        };
    }
}
