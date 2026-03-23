<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class JsonArrayParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!is_array($input)) {
            throw new FormatParserWrongInputTypeException('Array is expected.');
        }

        $components = [];

        foreach ($inputs['components'] ?? $input['c'] ?? [] as $component) {
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
            roundingApplication: $input['rounding']['a'] ?? $input['r']['a'],
            components: $components,
        );
    }
}
