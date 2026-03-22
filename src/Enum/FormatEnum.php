<?php

namespace RelMon\Enum;

use RelMon\FormatParser\JsonArrayParser;
use RelMon\FormatParser\JsonStringParser;
use RelMon\FormatParser\UriJsonParser;
use RelMon\FormatParser\UriMinimalisticParser;
use RelMon\FormatParser\UriXmlParser;
use RelMon\FormatParser\XmlDomDocumentParser;
use RelMon\FormatParser\XmlSimpleXmlParser;
use RelMon\FormatParser\XmlStringParser;

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
