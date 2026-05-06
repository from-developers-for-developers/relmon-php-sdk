<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriMinimalisticParser;
use PHPUnit\Framework\TestCase;

class UriMinimalisticParserTest extends TestCase
{
    public function testParseUriMinimalisticString(): void
    {
        $parser = new UriMinimalisticParser();
        $input = 'relmon-min://1.0;100.00;121.00;21.00';

        $dto = $parser->parse($input);

        $this->assertEquals('relmon@1.0', $dto->protocolIdentifier);
        $this->assertEquals('100.00', $dto->net);
        $this->assertEquals('121.00', $dto->gross);
        $this->assertEquals('21.00', $dto->tax);
    }

    public function testParseThrowsExceptionOnWrongPrefix(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new UriMinimalisticParser())->parse('relmon-json://1.0;100.00;121.00;21.00');
    }

    public function testParseThrowsExceptionOnEmptyPayload(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new UriMinimalisticParser())->parse('relmon-min://');
    }

    public function testParseThrowsExceptionOnInvalidFieldCount(): void
    {
        $this->expectException(FormatParserWrongInputTypeException::class);
        (new UriMinimalisticParser())->parse('relmon-min://1.0;100.00;121.00');
    }
}
