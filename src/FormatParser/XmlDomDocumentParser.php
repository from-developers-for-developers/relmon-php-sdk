<?php

namespace RelMon\FormatParser;

use RelMon\Dto\RelMonDto;

class XmlDomDocumentParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
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
