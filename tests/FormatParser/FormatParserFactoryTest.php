<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\Format;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserNotSupportedException;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserFactory;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserInterface;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserLocator;
use PHPUnit\Framework\TestCase;

class FormatParserFactoryTest extends TestCase
{
    public function testCreateFormatParserSuccess(): void
    {
        $mockParser = $this->createMock(FormatParserInterface::class);
        $locator = $this->createMock(FormatParserLocator::class);
        $locator->expects($this->once())
            ->method('getFormatParserByFormat')
            ->with('SomeFormat')
            ->willReturn($mockParser);

        $factory = new FormatParserFactory($locator);
        $parser = $factory->createFormatParser('SomeFormat');

        $this->assertSame($mockParser, $parser);
    }

    public function testCreateFormatParserThrowsExceptionOnAuto(): void
    {
        $locator = $this->createMock(FormatParserLocator::class);
        $factory = new FormatParserFactory($locator);

        $this->expectException(FormatParserNotSupportedException::class);
        $factory->createFormatParser(Format::AUTO);
    }
}
