<?php

namespace FromDevelopersForDevelopers\RelMon\Enum;

use FromDevelopersForDevelopers\RelMon\FormatParser\JsonArrayParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonStringParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriJsonParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriMinimalisticParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriXmlParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlDomDocumentParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlSimpleXmlParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlStringParser;

enum FormatEnum: string
{
    case AUTO = 'auto';
    case JSON_ARRAY = JsonArrayParser::class;
    case JSON_STRING = JsonStringParser::class;
    case XML_SIMPLE_XML = XmlSimpleXmlParser::class;
    case XML_DOM_DOCUMENT = XmlDomDocumentParser::class;
    case XML_STRING = XmlStringParser::class;
    case URI_JSON = UriJsonParser::class;
    case URI_XML = UriXmlParser::class;
    case URI_MINIMALISTIC = UriMinimalisticParser::class;
}
