<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\Format;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserNotSupportedException;

class FormatParserFactory
{
    public function __construct(private FormatParserLocator $formatParserLocator)
    {
    }

    public function createFormatParser(string $format): FormatParserInterface
    {
        if ($format === Format::AUTO) {
            throw new FormatParserNotSupportedException();
        }

        return $this->formatParserLocator->getFormatParserByFormat($format);
    }
}
