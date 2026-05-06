<?php

namespace FromDevelopersForDevelopers\RelMon;

use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserFactory;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserLocator;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonArrayParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonStringParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriJsonParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriMinimalisticParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriXmlParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlDomDocumentParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlSimpleXmlParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlStringParser;
use FromDevelopersForDevelopers\RelMon\Enum\Format;
use FromDevelopersForDevelopers\RelMon\Service\DerivationService;
use FromDevelopersForDevelopers\RelMon\Service\MinorsService;
use FromDevelopersForDevelopers\RelMon\Service\RelMonService;
use FromDevelopersForDevelopers\RelMon\Service\ValidationService;
use FromDevelopersForDevelopers\RelMon\ValueObject\RelMonObject;

final class RelMonFacade
{
    public static function build(mixed $input, string $format = Format::AUTO): RelMonObject
    {
        return self::createService()->build($input, $format);
    }

    private static function createService(): RelMonService
    {
        $formatParserLocator = new FormatParserLocator([
            new JsonArrayParser(),
            new JsonStringParser(),
            new XmlSimpleXmlParser(),
            new XmlDomDocumentParser(),
            new XmlStringParser(),
            new UriJsonParser(),
            new UriXmlParser(),
            new UriMinimalisticParser(),
        ]);

        return new RelMonService(
            new FormatParserFactory($formatParserLocator),
            new ValidationService(),
            new MinorsService(),
            new DerivationService(),
        );
    }
}
