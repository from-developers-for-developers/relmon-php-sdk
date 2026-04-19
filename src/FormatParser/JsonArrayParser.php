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

        $rounding = $input['rounding'] ?? $input['r'] ?? [];
        $mode = $rounding['mode'] ?? $rounding['m'] ?? null;
        $application = $rounding['application'] ?? $rounding['a'] ?? null;

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

        $scope = $input['scope'] ?? $input['s'] ?? null;
        if ($scope && !Scope::tryFrom($scope)) {
            throw new ValidationException([new ViolationDto("Invalid scope: $scope", 'scope')]);
        }

        $components = [];

        foreach ($input['components'] ?? $input['cs'] ?? [] as $component) {
            $components[] = new MonetaryComponentDto(
                net: $component['net'] ?? $component['n'],
                gross: $component['gross'] ?? $component['g'],
                tax: $component['tax'] ?? $component['t'],
                taxRate: $component['taxRate'] ?? $component['tr'],
                comment: $component['comment'] ?? $component['c'],
            );
        }

        return new RelMonDto(
            protocolIdentifier: $input['protocol'] ?? $input['p'],
            net: $input['net'] ?? $input['n'],
            gross: $input['gross'] ?? $input['g'],
            tax: $input['tax'] ?? $input['t'],
            taxRate: $input['taxRate'] ?? $input['tr'],
            unit: $input['unit'] ?? $input['u'],
            precision: $input['precision'] ?? $input['pr'],
            scope: $scope,
            roundingMode: $mode,
            roundingApplication: $application,
            components: $components,
        );
    }
}
