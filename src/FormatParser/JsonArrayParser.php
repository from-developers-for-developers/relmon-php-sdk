<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
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

        if (
            !empty($rounding)
            && (
                empty($rounding['mode'] ?? $rounding['m'])
                && empty($rounding['application'] ?? $rounding['a'])
            )
        ) {
            throw new ValidationException([
                new ViolationDto('If rounding is specified, either mode or application are required.', 'rounding'),
            ]);
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
            scope: $input['scope'] ?? $input['s'],
            roundingMode: $input['rounding']['mode'] ?? $input['r']['m'],
            roundingApplication: $input['rounding']['application'] ?? $input['r']['a'],
            components: $components,
        );
    }
}
