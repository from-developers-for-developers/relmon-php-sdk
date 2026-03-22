<?php

namespace FromDevelopersForDevelopers\RelMon\Tests;

use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;

class DummyMonetaryBasis implements MonetaryBasisInterface
{
    public function __construct(
        private null|string|int $net = null,
        private null|string|int $gross = null,
        private null|string|int $tax = null,
        private null|string|int $taxRate = null,
        private ?int $netInMinors = null,
        private ?int $grossInMinors = null,
        private ?int $taxInMinors = null,
        private ?int $taxRateInMinors = null,
    )
    {
    }

    public function getNet(): null|string|int
    {
        return $this->net;
    }

    public function getGross(): null|string|int
    {
        return $this->gross;
    }

    public function getTax(): null|string|int
    {
        return $this->tax;
    }

    public function getTaxRate(): null|string|int
    {
        return $this->taxRate;
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
}
