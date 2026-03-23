<?php

namespace FromDevelopersForDevelopers\RelMon;

use Decimal\Decimal;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Enum\ScopeEnum;

class RelMonObject
{
    public function __construct(
        private Decimal|int             $net,
        private Decimal|int             $gross,
        private Decimal|int             $tax,
        private ?Decimal                $taxRate = null,
        private ?string                 $unit = null,
        private ?int                    $precision = null,
        private ScopeEnum               $scope = ScopeEnum::ROOT,
        private RoundingModeEnum        $roundingMode = RoundingModeEnum::HALF_EVEN,
        private RoundingApplicationEnum $roundingApplication = RoundingApplicationEnum::TAX,
        private array                   $components = [],
    )
    {
    }

    public function getNet(): Decimal|int
    {
        return $this->net;
    }

    public function getGross(): Decimal|int
    {
        return $this->gross;
    }

    public function getTax(): Decimal|int
    {
        return $this->tax;
    }

    public function getTaxRate(): ?Decimal
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
