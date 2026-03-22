<?php

namespace FromDevelopersForDevelopers\RelMon\Enum;

enum RoundingModeEnum: string
{
    case HALF_AWAY_FROM_ZERO = 'haway';
    case HALF_TOWARDS_ZERO = 'hdown';
    case HALF_EVEN = 'heven';
    case UP = 'up';
    case DOWN = 'down';

    public function round(int|float $valueInMinors, int $precision = 0): int
    {
        return match ($this) {
            self::HALF_AWAY_FROM_ZERO => round($valueInMinors, $precision, PHP_ROUND_HALF_UP),
            self::HALF_TOWARDS_ZERO => round($valueInMinors, $precision, PHP_ROUND_HALF_DOWN),
            self::HALF_EVEN => round($valueInMinors, $precision, PHP_ROUND_HALF_EVEN),
            self::UP => $valueInMinors >= 0 ? ceil($valueInMinors) : floor($valueInMinors),
            self::DOWN => (int)$valueInMinors,
        };
    }
}
