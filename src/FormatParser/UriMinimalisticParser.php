<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class UriMinimalisticParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!str_starts_with($input, 'relmon-min://')) {
            throw new FormatParserWrongInputTypeException('Minimalistic URI notation is expected (relmon-min://...).');
        }

        return new RelMonDto(
            protocolIdentifier: 'relmon@1.0.0/3',
            net: null,
            gross: null,
            tax: null,
            taxRate: null,
            unit: null,
            precision: null,
            scope: null,
            roundingMode: null,
            roundingApplication: null,
            components: [],
        );
    }
}
