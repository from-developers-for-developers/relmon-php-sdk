<?php

namespace FromDevelopersForDevelopers\RelMon\Dto;

use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;

/** @internal */
class CanonicalMonetaryComponentDto implements MonetaryBasisInterface
{
    public function __construct(
        private MonetaryBasisInterface $basis,
        private ?string                $comment = null,
    )
    {
    }

    public function getNetInMinors(): ?int
    {
        return $this->basis->getNetInMinors();
    }

    public function getGrossInMinors(): ?int
    {
        return $this->basis->getGrossInMinors();
    }

    public function getTaxInMinors(): ?int
    {
        return $this->basis->getTaxInMinors();
    }

    public function getTaxRateInMinors(): ?int
    {
        return $this->basis->getTaxRateInMinors();
    }

    public function getPrecision(): int
    {
        return $this->basis->getPrecision();
    }

    public function getTaxRatePrecision(): int
    {
        return $this->basis->getTaxRatePrecision();
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
