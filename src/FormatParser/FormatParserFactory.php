<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\FormatEnum;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserNotSupportedException;

class FormatParserFactory
{
    public function __construct(private FormatParserLocator $formatParserLocator)
    {
    }

    public function createFormatParser(FormatEnum $formatEnum): FormatParserInterface
    {
        if ($formatEnum === FormatEnum::AUTO) {
            throw new FormatParserNotSupportedException();
        }

        return $this->formatParserLocator->getFormatParserByFormatEnum($formatEnum);
    }
}
