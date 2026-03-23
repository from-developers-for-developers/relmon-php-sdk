<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\MonetaryMinorsBasisInterface;

class MinorsService
{
    public function toMinors(
        MonetaryBasisInterface $basis,
        int                    $precision,
        ?int                   $taxRatePrecision = null
    ): MonetaryMinorsBasisInterface
    {
        // round() is used here to handle float imprecision, e.g. 20.40 * 100 = 2039.9999999999998
        $netInMinors = is_null($basis->getNet()) ? null : (int)round($basis->getNet() * (10 ** $precision));
        $grossInMinors = is_null($basis->getGross()) ? null : (int)round($basis->getGross() * (10 ** $precision));
        $taxInMinors = is_null($basis->getTax()) ? null : (int)round($basis->getTax() * (10 ** $precision));
        $taxRateInMinors = null;

        if (!is_null($basis->getTaxRate()) && !is_null($taxRatePrecision)) {
            $taxRateInMinors = (int)round($basis->getTaxRate() * (10 ** $taxRatePrecision));
        }

        return new class(
            $netInMinors,
            $grossInMinors,
            $taxInMinors,
            $taxRateInMinors,
            $taxRatePrecision
        ) implements MonetaryMinorsBasisInterface {
            public function __construct(
                private ?int $netInMinors,
                private ?int $grossInMinors,
                private ?int $taxInMinors,
                private ?int $taxRateInMinors,
                private ?int $taxRatePrecision,
            )
            {
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

            public function getTaxRatePrecision(): ?int
            {
                return $this->taxRatePrecision;
            }
        };
    }
}
