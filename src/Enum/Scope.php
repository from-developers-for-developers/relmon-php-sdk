<?php

namespace FromDevelopersForDevelopers\RelMon\Enum;

class Scope
{
    public const ROOT = 'r';
    public const COMPONENT = 'c';

    public static function values(): array
    {
        return [
            self::ROOT,
            self::COMPONENT,
        ];
    }

    public static function tryFrom(mixed $value): ?string
    {
        return in_array($value, self::values(), true) ? $value : null;
    }
}
