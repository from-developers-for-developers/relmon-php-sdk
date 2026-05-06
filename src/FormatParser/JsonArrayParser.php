<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;
use FromDevelopersForDevelopers\RelMon\Exception\ValidationException;

class JsonArrayParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!is_array($input)) {
            throw new FormatParserWrongInputTypeException('Array is expected.');
        }

        $rounding = $this->getAliasedValue($input, 'rounding', 'r') ?? [];
        $mode = is_array($rounding) ? $this->getAliasedValue($rounding, 'mode', 'm') : null;
        $application = is_array($rounding) ? $this->getAliasedValue($rounding, 'application', 'a') : null;

        if (
            !empty($rounding)
            && (empty($mode) || empty($application))
        ) {
            throw new ValidationException([
                new ViolationDto('If rounding is specified, either mode or application are required.', 'rounding'),
            ]);
        }

        if ($mode && !RoundingMode::tryFrom($mode)) {
            throw new ValidationException([new ViolationDto("Invalid rounding mode: $mode", 'rounding.mode')]);
        }

        if ($application && !RoundingApplication::tryFrom($application)) {
            throw new ValidationException([new ViolationDto("Invalid rounding application: $application", 'rounding.application')]);
        }

        $scope = $this->getAliasedValue($input, 'scope', 's');
        if ($scope && !Scope::tryFrom($scope)) {
            throw new ValidationException([new ViolationDto("Invalid scope: $scope", 'scope')]);
        }

        $components = [];

        foreach ($this->getAliasedValue($input, 'components', 'cs') ?? [] as $component) {
            $components[] = new MonetaryComponentDto(
                net: $this->getAliasedValue($component, 'net', 'n'),
                gross: $this->getAliasedValue($component, 'gross', 'g'),
                tax: $this->getAliasedValue($component, 'tax', 't'),
                taxRate: $this->getAliasedValue($component, 'taxRate', 'tr'),
                comment: $this->getAliasedValue($component, 'comment', 'c'),
            );
        }

        return new RelMonDto(
            protocolIdentifier: $this->getAliasedValue($input, 'protocol', 'p'),
            net: $this->getAliasedValue($input, 'net', 'n'),
            gross: $this->getAliasedValue($input, 'gross', 'g'),
            tax: $this->getAliasedValue($input, 'tax', 't'),
            taxRate: $this->getAliasedValue($input, 'taxRate', 'tr'),
            unit: $this->getAliasedValue($input, 'unit', 'u'),
            precision: $this->getAliasedValue($input, 'precision', 'pr'),
            scope: $scope,
            roundingMode: $mode,
            roundingApplication: $application,
            components: $components,
        );
    }

    private function getAliasedValue(array $input, string $fullName, string $compactName): mixed
    {
        if (array_key_exists($fullName, $input)) {
            return $input[$fullName];
        }

        if (array_key_exists($compactName, $input)) {
            return $input[$compactName];
        }

        return null;
    }
}
