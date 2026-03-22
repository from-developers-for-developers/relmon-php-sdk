<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;

class MinorsService
{
    public function toMinors(
        MonetaryBasisInterface $basis,
        int $precision,
        ?int $taxRatePrecision = null
    ): MonetaryBasisInterface
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
            $basis,
            $netInMinors,
            $grossInMinors,
            $taxInMinors,
            $taxRateInMinors
        ) implements MonetaryBasisInterface {
            public function __construct(
                private MonetaryBasisInterface $basis,
                private ?int                   $netInMinors,
                private ?int                   $grossInMinors,
                private ?int                   $taxInMinors,
                private ?int                   $taxRateInMinors,
            )
            {
            }

            public function getNet(): null|string|int
            {
                return $this->basis->getNet();
            }

            public function getGross(): null|string|int
            {
                return $this->basis->getGross();
            }

            public function getTax(): null|string|int
            {
                return $this->basis->getTax();
            }

            public function getTaxRate(): null|string|int
            {
                return $this->basis->getTaxRate();
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
        };
    }
}
