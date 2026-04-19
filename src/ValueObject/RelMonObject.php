<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Enum\ScopeEnum;

class RelMonObject
{
    const DETERMINISM_LEVEL_1 = 1;
    const DETERMINISM_LEVEL_2 = 1;
    const DETERMINISM_LEVEL_3 = 1;

    CONST ROUNDING_APPLICATION_TAX = 'tax';
    const ROUNDING_APPLICATION_TOTAL = 'total';

    const ROUNDING_MODE_HALF_AWAY_FROM_ZERO = 'haway';
    const ROUNDING_MODE_HALF_TOWARDS_ZERO = 'hzero';
    const ROUNDING_MODE_HALF_EVEN = 'heven';
    const ROUNDING_MODE_UP = 'up';
    const ROUNDING_MODE_DOWN = 'down';

    const SCOPE_ROOT = 'r';
    const SCOPE_COMPONENT = 'c';

    public function __construct(
        private int                     $net,
        private int                     $gross,
        private int                     $tax,
        private ?int                    $taxRate = null,
        private ?string                 $unit = null,
        private ?int                    $precision = null,
        private ScopeEnum               $scope = ScopeEnum::ROOT,
        private RoundingModeEnum        $roundingMode = RoundingModeEnum::HALF_EVEN,
        private RoundingApplicationEnum $roundingApplication = RoundingApplicationEnum::TAX,

        /** @var MonetaryComponent[] */
        private array                   $components = [],
    )
    {
    }

    public function getNet(): int
    {
        return $this->net;
    }

    public function getGross(): int
    {
        return $this->gross;
    }

    public function getTax(): int
    {
        return $this->tax;
    }

    public function getTaxRate(): ?int
    {
        return $this->taxRate;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function getScope(): ScopeEnum
    {
        return $this->scope;
    }

    public function getRoundingMode(): RoundingModeEnum
    {
        return $this->roundingMode;
    }

    public function getRoundingApplication(): RoundingApplicationEnum
    {
        return $this->roundingApplication;
    }

    public function getComponents(): array
    {
        return $this->components;
    }
}
