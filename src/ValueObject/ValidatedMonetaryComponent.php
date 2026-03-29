<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\Interface\MonetaryMinorsBasisInterface;

/** @internal */
class ValidatedMonetaryComponent implements MonetaryMinorsBasisInterface
{
    public function __construct(
        private readonly MonetaryMinorsBasisInterface $minorsBasis,
        private readonly ?string                      $comment = null,
    )
    {
    }

    public function getNetInMinors(): ?int
    {
        return $this->minorsBasis->getNetInMinors();
    }

    public function getGrossInMinors(): ?int
    {
        return $this->minorsBasis->getGrossInMinors();
    }

    public function getTaxInMinors(): ?int
    {
        return $this->minorsBasis->getTaxInMinors();
    }

    public function getTaxRateInMinors(): ?int
    {
        return $this->minorsBasis->getTaxRateInMinors();
    }

    public function getTaxRatePrecision(): int
    {
        return $this->minorsBasis->getTaxRatePrecision();
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
