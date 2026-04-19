<?php

namespace FromDevelopersForDevelopers\RelMon\Enum;

class DeterminismLevel
{
    public const DL1 = 1;
    public const DL2 = 2;
    public const DL3 = 3;

    public static function values(): array
    {
        return [
            self::DL1,
            self::DL2,
            self::DL3,
        ];
    }

    public static function tryFrom(mixed $value): ?int
    {
        return in_array($value, self::values(), true) ? (int)$value : null;
    }
}
