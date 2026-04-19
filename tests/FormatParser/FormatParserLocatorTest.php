<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Exception\FormatParserNotLocatedException;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserLocator;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonStringParser;
use PHPUnit\Framework\TestCase;

class FormatParserLocatorTest extends TestCase
{
    public function testGetFormatParserSuccess(): void
    {
        $jsonParser = new JsonStringParser();
        $locator = new FormatParserLocator([$jsonParser]);
        
        $parser = $locator->getFormatParserByFormat(JsonStringParser::class);
        
        $this->assertSame($jsonParser, $parser);
    }

    public function testGetFormatParserThrowsException(): void
    {
        $locator = new FormatParserLocator([]);
        
        $this->expectException(FormatParserNotLocatedException::class);
        $locator->getFormatParserByFormat('NonExistentParser');
    }
}
