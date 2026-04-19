<?php

namespace FromDevelopersForDevelopers\RelMon\Tests;

use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;

class DummyMonetaryBasis implements MonetaryBasisInterface
{
    public function __construct(
        private ?int $netInMinors = null,
        private ?int $grossInMinors = null,
        private ?int $taxInMinors = null,
        private ?int $taxRateInMinors = null,
        private int $precision = 2,
        private int $taxRatePrecision = 2,
    )
    {
    }

    public function getNetInMinors(): ?int
    {
        return $this->netInMinors;
    }

    public function getGrossInMinors(): ?int
    {
        return $this->grossInMinors;
    }

    public function getTaxInMinors(): ?int
    {
        return $this->taxInMinors;
    }

    public function getTaxRateInMinors(): ?int
    {
        return $this->taxRateInMinors;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getTaxRatePrecision(): int
    {
        return $this->taxRatePrecision;
    }
}
