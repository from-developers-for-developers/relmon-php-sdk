<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\FormatEnum;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserNotLocatedException;

class FormatParserLocator
{
    private array $formatParsers = [];

    public function __construct(iterable $formatParsers)
    {
        foreach ($formatParsers as $formatParser) {
            $this->formatParsers[$this->getRealClassName($formatParser)] = $formatParser;
        }
    }

    public function getFormatParsers(): array
    {
        return $this->formatParsers;
    }

    public function getFormatParserByFormatEnum(FormatEnum $formatEnum): FormatParserInterface
    {
        if (!isset($this->formatParsers[$formatEnum->value])) {
            throw new FormatParserNotLocatedException();
        }

        return $this->formatParsers[$formatEnum->value];
    }

    private function getRealClassName(object $object): string
    {
        $className = get_class($object);

        if ($className === 'Symfony\Component\VarExporter\LazyObjectInterface') {
            $reflectedObject = new \ReflectionClass($object);
            $className = $reflectedObject->getParentClass()->getName();
        }

        return $className;
    }
}
