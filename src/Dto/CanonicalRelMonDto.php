<?php

namespace FromDevelopersForDevelopers\RelMon\Dto;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Enum\ScopeEnum;
use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;

/** @internal */
class CanonicalRelMonDto implements MonetaryBasisInterface
{
    public function __construct(
        private ProtocolIdentifier      $protocolIdentifier,
        private ScopeEnum               $scope,
        private RoundingModeEnum        $roundingMode,
        private RoundingApplicationEnum $roundingApplication,
        private MonetaryBasisInterface  $basis,
        private int                     $precision,
        private int                     $taxRatePrecision,
        private ?string                 $unit = null,

        /** @var CanonicalMonetaryComponentDto[] */
        private array                   $components = [],
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

    public function getProtocolIdentifier(): ProtocolIdentifier
    {
        return $this->protocolIdentifier;
    }

    public function getScope(): ScopeEnum
    {
        return $this->scope;
    }

    public function getRoundingMode(): RoundingModeEnum
    {
        return $this->roundingMode;
    }

    public function getRoundingApplication(): RoundingApplicationEnum
    {
        return $this->roundingApplication;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getTaxRatePrecision(): int
    {
        return $this->taxRatePrecision;
    }

    public function getComponents(): array
    {
        return $this->components;
    }
}
