<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
use FromDevelopersForDevelopers\RelMon\Enum\DeterminismLevelEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Enum\ScopeEnum;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;
use FromDevelopersForDevelopers\RelMon\ValueObject\RelMonObject;

class ValidationService
{
    public function validate(ProtocolIdentifier $protocolIdentifier, RelMonDto $dto): array
    {
        $violations = [];

        if (!is_null($dto->precision) && $dto->precision < 0) {
            $violations[] = new ViolationDto('Precision must be greater or equal to zero.', 'precision');
        }

        if (!is_null($dto->scope) && ScopeEnum::tryFrom($dto->scope) === null) {
            $violations[] = new ViolationDto('Invalid scope.', 'scope');
        }

        if (!is_null($dto->roundingMode) && RoundingModeEnum::tryFrom($dto->roundingMode) === null) {
            $violations[] = new ViolationDto('Invalid rounding mode.', 'rounding.mode');
        }

        if (!is_null($dto->roundingApplication) && RoundingApplicationEnum::tryFrom($dto->roundingApplication) === null) {
            $violations[] = new ViolationDto('Invalid rounding application.', 'rounding.application');
        }

        if (!is_null($dto->scope) && $dto->scope === ScopeEnum::COMPONENT->value && empty($dto->components)) {
            $violations[] = new ViolationDto('Components are required if the scope is "c".', 'components');
        }

        return array_merge(
            $violations,
            $this->validateMonetaryBasisTypes($protocolIdentifier, $dto),
            $this->validateMonetaryBasis($protocolIdentifier, $dto),
            $this->validateGrossAndNet($dto),
        );
    }

    private function validateMonetaryBasisTypes(ProtocolIdentifier $protocolIdentifier, RelMonDto $dto): array
    {
        $fields = [$dto->getNet(), $dto->getGross(), $dto->getTax()];
        $signs = [];

        foreach ($dto->components as $component) {
            $fields = array_merge($fields, [$component->getNet(), $component->getGross(), $component->getTax()]);
        }

        foreach ($fields as $k => $field) {
            if (is_null($field)) {
                unset($fields[$k]);
            } else {
                $signs[] = $field < 0 ? '-' : '+';
            }
        }

        $types = array_unique(array_map('gettype', $fields));

        if (count($types) > 1) {
            return [new ViolationDto('Net, gross and tax fields of root and component levels must be of the same type.')];
        }

        $signs = array_unique($signs);

        if (count($signs) > 1) {
            return [new ViolationDto('Net, gross and tax fields of root and component levels must have the same sign.')];
        }

        if ($protocolIdentifier->isInMinorsMode() && $types[0] !== 'integer') {
            return [new ViolationDto('Net, gross and tax of root and component levels in minors mode must be of type integer.')];
        } elseif (!$protocolIdentifier->isInMinorsMode() && $types[0] !== 'string') {
            return [new ViolationDto('Net, gross and tax of root and component levels must be of type decimal.')];
        } elseif (!$protocolIdentifier->isInMinorsMode()) {
            $decimalPlaces = [];

            foreach ($fields as $field) {
                if (!preg_match('/^-?\d+\.(\d)*$/', $field, $matches)) {
                    return [new ViolationDto('Net, gross and tax of root and component levels must be of type decimal.')];
                }

                $decimalPlaces[] = (int)$matches[1];
            }

            if (!is_null($dto->precision) && max($decimalPlaces) > $dto->precision) {
                return [new ViolationDto('Decimal places of net, gross and tax values must be exceed the given precision.')];
            }
        }

        return [];
    }

    private function validateMonetaryBasis(
        ProtocolIdentifier             $protocolIdentifier,
        RelMonDto|MonetaryComponentDto $dto,
        string                         $violationField = ''
    ): array
    {
        if ($protocolIdentifier->getDeterminismLevel() === RelMonObject::DETERMINISM_LEVEL_1) {
            if (is_null($dto->getTaxRate())) {
                return [new ViolationDto('Tax rate must be specified for DL1.', "{$violationField}.taxRate")];
            }

            if (is_null($dto->getNet()) && is_null($dto->getGross())) {
                return [new ViolationDto('Net or gross must be specified for DL1.')];
            }
        }

        if ($protocolIdentifier->getDeterminismLevel() === RelMonObject::DETERMINISM_LEVEL_2) {
            if (is_null($dto->getTaxRate())) {
                return [new ViolationDto('Tax rate must be specified for DL2.', "{$violationField}.taxRate")];
            }

            if (is_null($dto->getNet()) || is_null($dto->getGross())) {
                return [new ViolationDto('Net and gross must be specified for DL2.')];
            }
        }

        if ($protocolIdentifier->getDeterminismLevel() === RelMonObject::DETERMINISM_LEVEL_3) {
            if (is_null($dto->getNet()) || is_null($dto->getGross()) || is_null($dto->getTax())) {
                return [new ViolationDto('Net, gross and tax must be specified for DL3.')];
            }
        }

        if (!is_null($dto->taxRate) && !preg_match('/^-?\d{1,3}\.\d{0,3}$/', $dto->taxRate)) {
            return [new ViolationDto('Tax rate must be of type decimal with maximum 3 decimal places.', "${violationField}.taxRate")];
        }

        if ($dto->scope === ScopeEnum::COMPONENT->value) {
            foreach ($dto->components as $k => $component) {
                $violations = $this->validateMonetaryBasis($protocolIdentifier, $component, "components.{$k}");

                if (!empty($violations)) {
                    return $violations;
                }

                if (
                    !is_null($component->getTaxRate())
                    && !is_null($dto->getTaxRate())
                    && (float)$component->getTaxRate() !== (float)$dto->getTaxRate()
                ) {
                    return [new ViolationDto(
                        'Tax rate on the root level must be the same on the component level.',
                        "components.{$k}.taxRate",
                    )];
                }
            }
        }

        return [];
    }

    private function validateGrossAndNet(RelMonDto|MonetaryComponentDto $dto, string $violationField = ''): array
    {
        // Most of the consistency among net/gross/tax is checked in DerivationService.
        if (
            !is_null($dto->getGross())
            && !is_null($dto->getNet())
            && (
                ($dto->getNet() < 0 && $dto->getGross() > $dto->getNet())
                || ($dto->getNet() > 0 && $dto->getGross() < $dto->getNet())
            )
        ) {
            return [new ViolationDto('Gross must be greater than or equal to net.', "{$violationField}.gross")];
        }

        if (!is_null($dto->getGross()) && !is_null($dto->getTax()) && $dto->getGross() < $dto->getTax()) {
            return [new ViolationDto('Tax must be less than gross.', "{$violationField}.tax")];
        }

        if (!is_null($dto->getNet()) && !is_null($dto->getTax()) && $dto->getNet() < $dto->getTax()) {
            return [new ViolationDto('Tax must be less than net.', "{$violationField}.tax")];
        }

        foreach ($dto->components as $k => $component) {
            $violations = $this->validateGrossAndNet($component, "components.{$k}");

            if (!empty($violations)) {
                return $violations;
            }
        }

        return [];
    }
}
