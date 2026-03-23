<?php

namespace FromDevelopersForDevelopers\RelMon\Dto;

use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;

/** @internal */
class MonetaryComponentDto implements MonetaryBasisInterface
{
    public function __construct(
        private null|string|int $net = null,
        private null|string|int $gross = null,
        private null|string|int $tax = null,
        private null|string|int $taxRate = null,
        private ?string         $comment = null,
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

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
