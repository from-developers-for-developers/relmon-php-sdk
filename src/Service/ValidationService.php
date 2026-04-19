<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
use FromDevelopersForDevelopers\RelMon\Enum\DeterminismLevel;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;

class ValidationService
{
    public function validate(ProtocolIdentifier $protocolIdentifier, RelMonDto $dto): array
    {
        $violations = [];

        if (!is_null($dto->precision) && $dto->precision < 0) {
            $violations[] = new ViolationDto('Precision must be greater or equal to zero.', 'precision');
        }

        if (!is_null($dto->scope) && Scope::tryFrom($dto->scope) === null) {
            $violations[] = new ViolationDto('Invalid scope.', 'scope');
        }

        if (!is_null($dto->roundingMode) && RoundingMode::tryFrom($dto->roundingMode) === null) {
            $violations[] = new ViolationDto('Invalid rounding mode.', 'roundingMode');
        }

        if (!is_null($dto->roundingApplication) && RoundingApplication::tryFrom($dto->roundingApplication) === null) {
            $violations[] = new ViolationDto('Invalid rounding application.', 'roundingApplication');
        }

        if (!is_null($dto->scope) && $dto->scope === Scope::COMPONENT && empty($dto->components)) {
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

        foreach ($dto->components as $component) {
            $fields = array_merge($fields, [$component->getNet(), $component->getGross(), $component->getTax()]);
        }

        $fields = array_filter($fields, fn($field) => !is_null($field));

        if (empty($fields)) {
            return [];
        }

        $signs = array_unique(array_map(fn($field) => (float)$field < 0 ? '-' : '+', $fields));

        if (count($signs) > 1) {
            return [new ViolationDto('Net, gross and tax fields of root and component levels must have the same sign.')];
        }

        $types = array_unique(array_map('gettype', $fields));

        if (count($types) > 1) {
            return [new ViolationDto('Net, gross and tax fields of root and component levels must be of the same type.')];
        }

        $type = reset($types);

        if ($protocolIdentifier->isInMinorsMode()) {
            if ($type !== 'integer') {
                return [new ViolationDto('Net, gross and tax of root and component levels in minors mode must be of type integer.')];
            }
        } else {
            if ($type !== 'string') {
                return [new ViolationDto('Net, gross and tax of root and component levels must be of type decimal (string).')];
            }

            $decimalPlaces = [];

            foreach ($fields as $field) {
                if (!preg_match('/^-?\d+\.\d+$/', $field)) {
                    return [new ViolationDto('Net, gross and tax of root and component levels must be of type decimal.')];
                }

                $parts = explode('.', $field);
                $decimalPlaces[] = strlen($parts[1]);
            }

            if (!is_null($dto->precision) && !empty($decimalPlaces) && max($decimalPlaces) > $dto->precision) {
                return [new ViolationDto('Decimal places of net, gross and tax values must not exceed the given precision.')];
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
        $determinismLevel = $protocolIdentifier->getDeterminismLevel();

        if ($determinismLevel === DeterminismLevel::DL1) {
            if (is_null($dto->getTaxRate())) {
                return [new ViolationDto('Tax rate must be specified for DL1.', trim("{$violationField}.taxRate", '.'))];
            }

            if (is_null($dto->getNet()) && is_null($dto->getGross())) {
                return [new ViolationDto('Net or gross must be specified for DL1.', trim("{$violationField}", '.'))];
            }
        } elseif ($determinismLevel === DeterminismLevel::DL2) {
            if (is_null($dto->getTaxRate())) {
                return [new ViolationDto('Tax rate must be specified for DL2.', trim("{$violationField}.taxRate", '.'))];
            }

            if (is_null($dto->getNet()) || is_null($dto->getGross())) {
                return [new ViolationDto('Net and gross must be specified for DL2.', trim("{$violationField}", '.'))];
            }
        } elseif ($determinismLevel === DeterminismLevel::DL3) {
            if (is_null($dto->getNet()) && is_null($dto->getGross())) {
                return [new ViolationDto('Net or gross must be specified for DL3.', trim("{$violationField}", '.'))];
            }

            if (is_null($dto->getTax())) {
                return [new ViolationDto('Tax must be specified for DL3.', trim("{$violationField}.tax", '.'))];
            }
        }

        if (!is_null($dto->getTaxRate()) && !preg_match('/^\d+(\.\d+)?$/', (string)$dto->getTaxRate())) {
            return [new ViolationDto('Tax rate must be a non-negative decimal.', trim("{$violationField}.taxRate", '.'))];
        }

        if ($dto instanceof RelMonDto && $dto->scope === Scope::COMPONENT) {
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
        $net = $dto->getNet();
        $gross = $dto->getGross();
        $tax = $dto->getTax();

        if (!is_null($net) && !is_null($gross)) {
            if ((float)$net >= 0 && (float)$gross < (float)$net) {
                return [new ViolationDto('Gross must be greater than or equal to net for positive amounts.', trim("{$violationField}.gross", '.'))];
            }

            if ((float)$net < 0 && (float)$gross > (float)$net) {
                return [new ViolationDto('Gross must be less than or equal to net for negative amounts.', trim("{$violationField}.gross", '.'))];
            }
        }

        if (!is_null($gross) && !is_null($tax)) {
            if ((float)$gross >= 0 && (float)$tax > (float)$gross) {
                return [new ViolationDto('Tax must be less than or equal to gross for positive amounts.', trim("{$violationField}.tax", '.'))];
            }

            if ((float)$gross < 0 && (float)$tax < (float)$gross) {
                return [new ViolationDto('Tax must be greater than or equal to gross for negative amounts.', trim("{$violationField}.tax", '.'))];
            }
        }

        if ($dto instanceof RelMonDto) {
            foreach ($dto->components as $k => $component) {
                $violations = $this->validateGrossAndNet($component, "components.{$k}");

                if (!empty($violations)) {
                    return $violations;
                }
            }
        }

        return [];
    }
}
