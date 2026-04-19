<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;

interface FormatParserInterface
{
    const AUTO = 'auto';
    const JSON_ARRAY = JsonArrayParser::class;
    const JSON_STRING = JsonStringParser::class;
    const XML_SIMPLE_XML = XmlSimpleXmlParser::class;
    const XML_DOM_DOCUMENT = XmlDomDocumentParser::class;
    const XML_STRING = XmlStringParser::class;
    const URI_JSON = UriJsonParser::class;
    const URI_XML = UriXmlParser::class;
    const URI_MINIMALISTIC = UriMinimalisticParser::class;

    public function parse(mixed $input): RelMonDto;
}
