<?php

namespace FromDevelopersForDevelopers\RelMon\Dto;

use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;

/** @internal */
class RelMonDto implements MonetaryBasisInterface
{
    public function __construct(
        public string          $protocolIdentifier,
        public null|string|int $net = null,
        public null|string|int $gross = null,
        public null|string|int $tax = null,
        public null|string|int $taxRate = null,
        public ?string         $unit = null,
        public ?int            $precision = null,
        public ?string         $scope = null,
        public ?string         $roundingMode = null,
        public ?string         $roundingApplication = null,

        /** @var MonetaryComponentDto[] */
        public array           $components = [],
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
}
