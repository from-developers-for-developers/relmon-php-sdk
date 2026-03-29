<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Enum\ScopeEnum;

class RelMonObject
{
    public function __construct(
        private readonly int                     $net,
        private readonly int                     $gross,
        private readonly int                     $tax,
        private readonly ?int                    $taxRate = null,
        private readonly ?string                 $unit = null,
        private readonly ?int                    $precision = null,
        private readonly ScopeEnum               $scope = ScopeEnum::ROOT,
        private readonly RoundingModeEnum        $roundingMode = RoundingModeEnum::HALF_EVEN,
        private readonly RoundingApplicationEnum $roundingApplication = RoundingApplicationEnum::TAX,
        private readonly array                   $components = [],
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
