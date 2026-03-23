<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\MonetaryMinorsBasisInterface;

/** @internal */
class DerivedResult implements MonetaryMinorsBasisInterface
{
    public function __construct(
        private int  $net,
        private int  $gross,
        private int  $tax,
        private ?int $taxRate = null,
        private ?int $taxRatePrecision = null,
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

    public function getTaxRatePrecision(): ?int
    {
        return $this->taxRatePrecision;
    }
}
