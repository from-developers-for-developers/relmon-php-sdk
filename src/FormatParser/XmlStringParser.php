<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class XmlStringParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!is_string($input)) {
            throw new FormatParserWrongInputTypeException('XML as string is expected.');
        }

        if (!str_starts_with($input, '<')) {
            throw new FormatParserWrongInputTypeException('XML as string is expected.');
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($input);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$xml instanceof \SimpleXMLElement) {
            throw new FormatParserWrongInputTypeException('Could not parse XML string.');
        }

        return (new XmlSimpleXmlParser())->parse($xml);
    }
}
