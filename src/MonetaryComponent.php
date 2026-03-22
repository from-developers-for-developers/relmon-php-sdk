<?php

namespace RelMon;

use Decimal\Decimal;

class MonetaryComponent
{
    public function __construct(
        private Decimal|int $net,
        private Decimal|int $gross,
        private Decimal|int $tax,
        private ?Decimal    $taxRate = null,
        private ?string     $comment = null,
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

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
