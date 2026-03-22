<?php

namespace RelMon\Dto;

use RelMon\MonetaryBasisInterface;

class MonetaryComponentDto implements MonetaryBasisInterface
{
    private ?int $netInMinors = null;
    private ?int $grossInMinors = null;
    private ?int $taxInMinors = null;
    private ?int $taxRateInMinors = null;

    public function __construct(
        public null|string|int $net = null,
        public null|string|int $gross = null,
        public null|string|int $tax = null,
        public null|string|int $taxRate = null,
        public ?string         $comment = null,
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

    public function setMinors(MonetaryBasisInterface $basis): self
    {
        $this->netInMinors = $basis->getNetInMinors();
        $this->grossInMinors = $basis->getGrossInMinors();
        $this->taxInMinors = $basis->getTaxInMinors();
        $this->taxRateInMinors = $basis->getTaxRateInMinors();

        return $this;
    }
}
