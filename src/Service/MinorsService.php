<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;

class MinorsService
{
    public function toMinors(
        RelMonDto|MonetaryComponentDto $dto,
        int $precision,
        int $taxRatePrecision
    ): MonetaryBasisInterface
    {
        // round() is used here to handle float imprecision, e.g. 20.40 * 100 = 2039.9999999999998
        $netInMinors = is_null($dto->getNet()) ? null : (int)round($dto->getNet() * (10 ** $precision));
        $grossInMinors = is_null($dto->getGross()) ? null : (int)round($dto->getGross() * (10 ** $precision));
        $taxInMinors = is_null($dto->getTax()) ? null : (int)round($dto->getTax() * (10 ** $precision));
        $taxRateInMinors = null;

        if (!is_null($dto->getTaxRate())) {
            $taxRateInMinors = (int)round($dto->getTaxRate() * (10 ** $taxRatePrecision));
        }

        return new class(
            $netInMinors,
            $grossInMinors,
            $taxInMinors,
            $taxRateInMinors,
            $precision,
            $taxRatePrecision
        ) implements MonetaryBasisInterface {
            public function __construct(
                private ?int $netInMinors,
                private ?int $grossInMinors,
                private ?int $taxInMinors,
                private ?int $taxRateInMinors,
                private int $precision,
                private int $taxRatePrecision,
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

            public function getPrecision(): int
            {
                return $this->precision;
            }

            public function getTaxRatePrecision(): int
            {
                return $this->taxRatePrecision;
            }
        };
    }
}
