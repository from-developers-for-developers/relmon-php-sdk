<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class XmlDomDocumentParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!$input instanceof \DOMDocument) {
            throw new FormatParserWrongInputTypeException('DOMDocument instance is expected.');
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
