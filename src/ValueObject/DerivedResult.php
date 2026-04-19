<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;

/** @internal */
class DerivedResult implements MonetaryBasisInterface
{
    public function __construct(
        private int  $net,
        private int  $gross,
        private int  $tax,
        private int  $precision,
        private int  $taxRatePrecision,
        private ?int $taxRate = null,
    )
    {
    }

    public function getNetInMinors(): ?int
    {
        return $this->net;
    }

    public function getGrossInMinors(): ?int
    {
        return $this->gross;
    }

    public function getTaxInMinors(): ?int
    {
        return $this->tax;
    }

    public function getTaxRateInMinors(): ?int
    {
        return $this->taxRate;
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
