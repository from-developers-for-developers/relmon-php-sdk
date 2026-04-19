<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\Format;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserNotLocatedException;

class FormatParserLocator
{
    /** @var FormatParserInterface[] */
    private array $formatParsers = [];

    public function __construct(iterable $formatParsers)
    {
        foreach ($formatParsers as $formatParser) {
            $this->addFormatParser($formatParser);
        }
    }

    public function addFormatParser(FormatParserInterface $formatParser): void
    {
        $this->formatParsers[get_class($formatParser)] = $formatParser;
    }

    public function getFormatParserByFormat(string $format): FormatParserInterface
    {
        if (!isset($this->formatParsers[$format])) {
            throw new FormatParserNotLocatedException();
        }

        return $this->formatParsers[$format];
    }
}
