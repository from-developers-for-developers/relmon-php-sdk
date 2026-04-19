<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

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
}
