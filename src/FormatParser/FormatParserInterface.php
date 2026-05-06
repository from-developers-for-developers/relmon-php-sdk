<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;

interface FormatParserInterface
{
    public const AUTO = 'auto';
    public const JSON_ARRAY = JsonArrayParser::class;
    public const JSON_STRING = JsonStringParser::class;
    public const XML_SIMPLE_XML = XmlSimpleXmlParser::class;
    public const XML_DOM_DOCUMENT = XmlDomDocumentParser::class;
    public const XML_STRING = XmlStringParser::class;
    public const URI_JSON = UriJsonParser::class;
    public const URI_XML = UriXmlParser::class;
    public const URI_MINIMALISTIC = UriMinimalisticParser::class;

    public function parse(mixed $input): RelMonDto;
}
