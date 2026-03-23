<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Enum\ScopeEnum;
use FromDevelopersForDevelopers\RelMon\MonetaryMinorsBasisInterface;
use FromDevelopersForDevelopers\RelMon\ProtocolIdentifier;

/** @internal */
class ValidatedRelMon implements MonetaryMinorsBasisInterface
{
    public function __construct(
        private ProtocolIdentifier           $protocolIdentifier,
        private ScopeEnum                    $scope,
        private RoundingModeEnum             $roundingMode,
        private RoundingApplicationEnum      $roundingApplication,
        private MonetaryMinorsBasisInterface $minorsBasis,
        private ?string                      $unit = null,
        private ?int                         $precision = null,
        private ?int                         $taxRatePrecision = null,

        /** @var ValidatedMonetaryComponent[] */
        private array                        $components = [],
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

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function getTaxRatePrecision(): ?int
    {
        return $this->taxRatePrecision;
    }

    public function getComponents(): array
    {
        return $this->components;
    }
}
