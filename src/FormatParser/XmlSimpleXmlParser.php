<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class XmlSimpleXmlParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!$input instanceof \SimpleXMLElement) {
            throw new FormatParserWrongInputTypeException('SimpleXMLElement instance is expected.');
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
