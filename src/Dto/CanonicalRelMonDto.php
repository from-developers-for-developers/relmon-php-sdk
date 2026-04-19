<?php

namespace FromDevelopersForDevelopers\RelMon\Dto;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;

class CanonicalRelMonDto implements MonetaryBasisInterface
{
    public function __construct(
        private ProtocolIdentifier      $protocolIdentifier,
        private string                  $scope,
        private string                  $roundingMode,
        private string                  $roundingApplication,
        private MonetaryBasisInterface  $basis,
        private int                     $precision,
        private int                     $taxRatePrecision,
        private ?string                 $unit,

        /** @var CanonicalMonetaryComponentDto[] */
        private array                   $components = [],
    )
    {
    }

    public function getProtocolIdentifier(): ProtocolIdentifier
    {
        return $this->protocolIdentifier;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getRoundingMode(): string
    {
        return $this->roundingMode;
    }

    public function getRoundingApplication(): string
    {
        return $this->roundingApplication;
    }

    public function getBasis(): MonetaryBasisInterface
    {
        return $this->basis;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getTaxRatePrecision(): int
    {
        return $this->taxRatePrecision;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @return CanonicalMonetaryComponentDto[]
     */
    public function getComponents(): array
    {
        return $this->components;
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
}
