<?php

namespace FromDevelopersForDevelopers\RelMon\Dto;

class DerivedResultDto
{
    public function __construct(
        private int $net,
        private int $gross,
        private int $tax,
        private ?int $taxRate,
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
}
