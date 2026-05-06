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

        if (!$input->documentElement instanceof \DOMElement) {
            throw new FormatParserWrongInputTypeException('DOMDocument must contain a root element.');
        }

        $xml = simplexml_import_dom($input->documentElement);

        if (!$xml instanceof \SimpleXMLElement) {
            throw new FormatParserWrongInputTypeException('Could not parse DOMDocument.');
        }

        return (new XmlSimpleXmlParser())->parse($xml);
    }
}
