<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

class MonetaryComponent
{
    public function __construct(
        private string|int $net,
        private string|int $gross,
        private string|int $tax,
        private ?string    $taxRate = null,
        private ?string    $comment = null,
    )
    {
    }

    public function getNet(): string|int
    {
        return $this->net;
    }

    public function getGross(): string|int
    {
        return $this->gross;
    }

    public function getTax(): string|int
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
