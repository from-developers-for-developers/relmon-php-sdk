<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

class MonetaryComponent
{
    public function __construct(
        private ?int    $net,
        private ?int    $gross,
        private ?int    $tax,
        private ?string $taxRate = null,
        private ?string $comment = null,
    )
    {
    }

    public function getNet(): ?int
    {
        return $this->net;
    }

    public function getGross(): ?int
    {
        return $this->gross;
    }

    public function getTax(): ?int
    {
        return $this->tax;
    }

    public function getTaxRate(): ?string
    {
        return $this->taxRate;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
