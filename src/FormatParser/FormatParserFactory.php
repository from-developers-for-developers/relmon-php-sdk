<?php

namespace RelMon\FormatParser;

use RelMon\Enum\FormatEnum;
use RelMon\Exception\FormatParserNotSupportedException;

class FormatParserFactory
{
    public function __construct(private readonly FormatParserLocator $formatParserLocator)
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
