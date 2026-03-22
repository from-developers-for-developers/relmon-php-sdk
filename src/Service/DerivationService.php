<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\Dto\DerivedResultDto;
use FromDevelopersForDevelopers\RelMon\Enum\DeterminismLevelEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Exception\DerivationException;
use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\ProtocolIdentifier;

class DerivationService
{
    public function derive(
        MonetaryBasisInterface  $basis,
        ProtocolIdentifier      $protocolIdentifier,
        RoundingModeEnum        $roundingMode,
        RoundingApplicationEnum $roundingApplication,
        ?int                    $taxRatePrecision,
    ): DerivedResultDto
    {
        return match ($protocolIdentifier->getDeterminismLevel()) {
            DeterminismLevelEnum::DL3 => $this->deriveDeterminismLevelThree(
                $basis,
                $roundingMode,
                $roundingApplication,
                $taxRatePrecision,
            ),

            DeterminismLevelEnum::DL2 => $this->deriveDeterminismLevelTwo(
                $basis,
                $roundingMode,
                $roundingApplication,
                $taxRatePrecision,
            ),

            DeterminismLevelEnum::DL1 => $this->deriveDeterminismLevelOne(
                $basis,
                $roundingMode,
                $roundingApplication,
                $taxRatePrecision,
            ),
        };
    }

    private function deriveDeterminismLevelThree(
        MonetaryBasisInterface  $basis,
        RoundingModeEnum        $roundingMode,
        RoundingApplicationEnum $roundingApplication,
        ?int                    $taxRatePrecision
    ): DerivedResultDto
    {
        if (
            is_null($basis->getNetInMinors())
            || is_null($basis->getGrossInMinors())
            || is_null($basis->getTaxInMinors())
        ) {
            throw new DerivationException('Net, gross and tax must be specified for DL3.');
        }

        if ($basis->getGrossInMinors() < $basis->getNetInMinors()) {
            throw new DerivationException('Gross must be greater then or equal to net.');
        }

        if ($basis->getNetInMinors() + $basis->getTaxInMinors() !== $basis->getGrossInMinors()) {
            throw new DerivationException('Net + tax must be equal to gross.');
        }

        if (!is_null($basis->getTaxRateInMinors()) && !is_null($taxRatePrecision)) {
            $this->validateReconstructedTax(
                $basis->getNetInMinors(),
                $basis->getGrossInMinors(),
                $basis->getTaxInMinors(),
                $basis->getTaxRateInMinors(),
                $taxRatePrecision,
                $roundingMode,
                $roundingApplication,
            );
        }

        return new DerivedResultDto(
            $basis->getNetInMinors(),
            $basis->getGrossInMinors(),
            $basis->getTaxInMinors(),
            $basis->getTaxRateInMinors()
        );
    }

    private function deriveDeterminismLevelTwo(
        MonetaryBasisInterface  $basis,
        RoundingModeEnum        $roundingMode,
        RoundingApplicationEnum $roundingApplication,
        int                     $taxRatePrecision,
    ): DerivedResultDto
    {
        $net = $basis->getNetInMinors();
        $gross = $basis->getGrossInMinors();
        $taxRate = $basis->getTaxRateInMinors();

        if (is_null($net) || is_null($gross) || is_null($taxRate)) {
            throw new DerivationException('Net, gross and tax rate must be specified for DL2.');
        }

        if ($gross < $net) {
            throw new DerivationException('Gross must be greater then or equal to net.');
        }

        $this->validateReconstructedTax(
            $net,
            $gross,
            $tax = $basis->getGrossInMinors() - $basis->getNetInMinors(),
            $taxRate,
            $taxRatePrecision,
            $roundingMode,
            $roundingApplication,
        );

        return new DerivedResultDto($net, $gross, $tax, $taxRate);
    }

    private function deriveDeterminismLevelOne(
        MonetaryBasisInterface  $basis,
        RoundingModeEnum        $roundingMode,
        RoundingApplicationEnum $roundingApplication,
        ?int                    $taxRatePrecision,
    ): DerivedResultDto
    {
        $taxRate = $basis->getTaxRateInMinors();

        if (is_null($taxRatePrecision) || is_null($taxRate)) {
            throw new DerivationException('Tax rate must be specified for DL1.');
        }

        $net = $basis->getNetInMinors();
        $gross = $basis->getGrossInMinors();
        $calculatedNet = $net;
        $calculatedGross = $gross;

        if (is_null($net) && is_null($gross)) {
            throw new DerivationException('Net or gross must be specified for DL1.');
        } elseif (!is_null($net) && !is_null($gross) && $gross < $net) {
            throw new DerivationException('Gross must be greater then or equal to net.');
        }

        $taxRateDivisor = 100 * (10 ** $taxRatePrecision);

        if ($roundingApplication === RoundingApplicationEnum::TAX) {
            if (!is_null($net)) {
                $tax = $roundingMode->round($net * ($taxRate / $taxRateDivisor));
                $calculatedGross = $net + $tax;
            } else {
                $tax = $roundingMode->round($gross * $taxRate / ($taxRateDivisor + $taxRate));
                $calculatedNet = $gross - $tax;
            }
        } else {
            if (!is_null($net)) {
                $calculatedGross = $roundingMode->round($net * ($taxRate / $taxRateDivisor + 1));
                $tax = $calculatedGross - $net;
            } else {
                $calculatedNet = $roundingMode->round($gross / ($taxRate / $taxRateDivisor + 1));
                $tax = $gross - $calculatedNet;
            }
        }

        if ((!is_null($net) && $calculatedNet !== $net) || (!is_null($gross) && $calculatedGross !== $gross)) {
            throw new DerivationException('Calculated net/gross must be equal to the explicitly defined net/gross.');
        }

        if ($calculatedNet + $tax !== $calculatedGross) {
            throw new DerivationException('Net + tax must be equal to gross.');
        }

        return new DerivedResultDto($calculatedNet, $calculatedGross, $tax, $taxRate);
    }

    private function validateReconstructedTax(
        int                     $netInMinors,
        int                     $grossInMinors,
        int                     $taxInMinors,
        int                     $taxRateInMinors,
        int                     $taxRatePrecision,
        RoundingModeEnum        $roundingMode,
        RoundingApplicationEnum $roundingApplication,
    ): void
    {
        $taxInMinors ??= $grossInMinors - $netInMinors;
        $taxRateDivisor = 100 * (10 ** $taxRatePrecision);
        $reconstructedTax = $roundingApplication === RoundingApplicationEnum::TAX
            ? $roundingMode->round($netInMinors * ($taxRateInMinors / $taxRateDivisor))
            : $roundingMode->round($netInMinors * ($taxRateInMinors / $taxRateDivisor + 1)) - $netInMinors;

        if ($taxInMinors !== $reconstructedTax) {
            throw new DerivationException('The reconstruction of the tax amount has failed.');
        }
    }
}
