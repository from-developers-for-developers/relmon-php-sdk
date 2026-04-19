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

class Format
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

    public static function values(): array
    {
        return [
            self::AUTO,
            self::JSON_ARRAY,
            self::JSON_STRING,
            self::XML_SIMPLE_XML,
            self::XML_DOM_DOCUMENT,
            self::XML_STRING,
            self::URI_JSON,
            self::URI_XML,
            self::URI_MINIMALISTIC,
        ];
    }

    public static function tryFrom(mixed $value): ?string
    {
        return in_array($value, self::values(), true) ? $value : null;
    }
}
