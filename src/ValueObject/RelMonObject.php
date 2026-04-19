<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;

class RelMonObject
{
    public function __construct(
        private int    $net,
        private int    $gross,
        private int    $tax,
        private ?int   $taxRate = null,
        private ?string $unit = null,
        private ?int   $precision = null,
        private string $scope = Scope::ROOT,
        private string $roundingMode = RoundingMode::HALF_EVEN,
        private string $roundingApplication = RoundingApplication::TAX,

        /** @var MonetaryComponent[] */
        private array  $components = [],
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

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getRoundingMode(): string
    {
        return $this->roundingMode;
    }

    public function getRoundingApplication(): string
    {
        return $this->roundingApplication;
    }

    public function getComponents(): array
    {
        return $this->components;
    }
}
