<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\MonetaryMinorsBasisInterface;

/** @internal */
class ValidatedMonetaryComponent implements MonetaryMinorsBasisInterface
{
    public function __construct(
        private MonetaryMinorsBasisInterface $minorsBasis,
        private ?int                         $taxRatePrecision = null,
        private ?string                      $comment = null,
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

    public function getTaxRatePrecision(): ?int
    {
        return $this->taxRatePrecision;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
