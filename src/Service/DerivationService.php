<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\Dto\CanonicalRelMonDto;
use FromDevelopersForDevelopers\RelMon\Enum\DeterminismLevelEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Exception\DerivationException;
use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\ValueObject\DerivedResult;
use FromDevelopersForDevelopers\RelMon\ValueObject\RelMonObject;

class DerivationService
{
    public function derive(CanonicalRelMonDto $relmon, MonetaryBasisInterface $basis): DerivedResult
    {
        return match ($relmon->getProtocolIdentifier()->getDeterminismLevel()) {
            RelMonObject::DETERMINISM_LEVEL_1 => $this->deriveDeterminismLevelThree($relmon, $basis),
            RelMonObject::DETERMINISM_LEVEL_2 => $this->deriveDeterminismLevelTwo($relmon, $basis),
            RelMonObject::DETERMINISM_LEVEL_3 => $this->deriveDeterminismLevelOne($relmon, $basis),
        };
    }

    private function deriveDeterminismLevelThree(
        CanonicalRelMonDto              $relmon,
        MonetaryBasisInterface $basis,
    ): DerivedResult
    {
        if (
            is_null($basis->getTaxInMinors())
            || (is_null($basis->getNetInMinors()) && is_null($basis->getGrossInMinors()))
        ) {
            throw new DerivationException('Net + tax and/or gross + tax must be specified for DL3.');
        }

        if (
            !is_null($basis->getNetInMinors()) && !is_null($basis->getGrossInMinors())
            && (
                ($basis->getNetInMinors() < 0 && $basis->getGrossInMinors() > $basis->getNetInMinors())
                || ($basis->getNetInMinors() > 0 && $basis->getGrossInMinors() < $basis->getNetInMinors())
            )
        ) {
            throw new DerivationException('Gross must be greater then or equal to net.');
        }

        if ($basis->getNetInMinors() + $basis->getTaxInMinors() !== $basis->getGrossInMinors()) {
            throw new DerivationException('Net + tax must be equal to gross.');
        }

        if (!is_null($basis->getTaxRateInMinors())) {
            $this->validateReconstructedTax(
                $basis->getNetInMinors(),
                $basis->getGrossInMinors(),
                $basis->getTaxInMinors(),
                $basis->getTaxRateInMinors(),
                $relmon->getTaxRatePrecision(),
                $relmon->getRoundingMode(),
                $relmon->getRoundingApplication(),
            );
        }

        return new DerivedResult(
            $basis->getNetInMinors(),
            $basis->getGrossInMinors(),
            $basis->getTaxInMinors(),
            $basis->getPrecision(),
            $basis->getTaxRatePrecision(),
            $basis->getTaxRateInMinors(),
        );
    }

    private function deriveDeterminismLevelTwo(
        CanonicalRelMonDto              $relmon,
        MonetaryBasisInterface $basis,
    ): DerivedResult
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
            $relmon->getTaxRatePrecision(),
            $relmon->getRoundingMode(),
            $relmon->getRoundingApplication(),
        );

        return new DerivedResult($net, $gross, $tax, $basis->getPrecision(), $basis->getTaxRatePrecision(), $taxRate);
    }

    private function deriveDeterminismLevelOne(
        CanonicalRelMonDto              $relmon,
        MonetaryBasisInterface $basis,
    ): DerivedResult
    {
        $taxRate = $basis->getTaxRateInMinors();

        if (is_null($taxRate)) {
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

        $taxRateDivisor = 100 * (10 ** $basis->getTaxRatePrecision());

        if ($relmon->getRoundingApplication() === RoundingApplicationEnum::TAX) {
            if (!is_null($net)) {
                $tax = $relmon->getRoundingMode()->round($net * ($taxRate / $taxRateDivisor));
                $calculatedGross = $net + $tax;
            } else {
                $tax = $relmon->getRoundingMode()->round($gross * $taxRate / ($taxRateDivisor + $taxRate));
                $calculatedNet = $gross - $tax;
            }
        } else {
            if (!is_null($net)) {
                $calculatedGross = $relmon->getRoundingMode()->round($net * ($taxRate / $taxRateDivisor + 1));
                $tax = $calculatedGross - $net;
            } else {
                $calculatedNet = $relmon->getRoundingMode()->round($gross / ($taxRate / $taxRateDivisor + 1));
                $tax = $gross - $calculatedNet;
            }
        }

        if ((!is_null($net) && $calculatedNet !== $net) || (!is_null($gross) && $calculatedGross !== $gross)) {
            throw new DerivationException('Calculated net/gross must be equal to the explicitly defined net/gross.');
        }

        if ($calculatedNet + $tax !== $calculatedGross) {
            throw new DerivationException('Net + tax must be equal to gross.');
        }

        return new DerivedResult(
            $calculatedNet,
            $calculatedGross,
            $tax,
            $basis->getPrecision(),
            $basis->getTaxRatePrecision(),
            $taxRate,
        );
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
