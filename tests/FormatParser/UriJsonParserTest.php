<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriJsonParser;
use PHPUnit\Framework\TestCase;

class UriJsonParserTest extends TestCase
{
    public function testParseUriJsonString(): void
    {
        $parser = new UriJsonParser();
        $json = json_encode([
            'p' => '1.0',
            'n' => '100.00',
            'g' => '121.00',
            't' => '21.00',
            'tr' => '21.00',
            'u' => 'EUR',
            'pr' => '2',
            's' => Scope::COMPONENT,
            'r' => [
                'm' => RoundingMode::UP,
                'a' => RoundingApplication::TAX
            ],
            'cs' => [
                [
                    'n' => '100.00',
                    'g' => '121.00',
                    't' => '21.00',
                    'tr' => '21.00',
                    'c' => 'Test component'
                ]
            ]
        ]);
        $input = 'relmon-json://' . base64_encode($json);

        $dto = $parser->parse($input);
        
        $this->assertEquals('1.0', $dto->protocolIdentifier);
        $this->assertEquals('100.00', $dto->net);
        $this->assertEquals('121.00', $dto->gross);
        $this->assertEquals('21.00', $dto->tax);
        $this->assertEquals('21.00', $dto->taxRate);
        $this->assertEquals('EUR', $dto->unit);
        $this->assertEquals('2', $dto->precision);
        $this->assertEquals(Scope::COMPONENT, $dto->scope);
        $this->assertEquals(RoundingMode::UP, $dto->roundingMode);
        $this->assertEquals(RoundingApplication::TAX, $dto->roundingApplication);
        $this->assertCount(1, $dto->components);

        $component = $dto->components[0];
        $this->assertEquals('100.00', $component->getNet());
        $this->assertEquals('121.00', $component->getGross());
        $this->assertEquals('21.00', $component->getTax());
        $this->assertEquals('21.00', $component->getTaxRate());
        $this->assertEquals('Test component', $component->getComment());
    }

    public function testParseUriJsonFullNotationString(): void
    {
        $parser = new UriJsonParser();
        $json = json_encode([
            'protocol' => '1.0',
            'net' => '100.00',
            'gross' => '121.00',
            'tax' => '21.00',
            'taxRate' => '21.00',
            'unit' => 'EUR',
            'precision' => '2',
            'scope' => Scope::ROOT,
            'rounding' => [
                'mode' => RoundingMode::UP,
                'application' => RoundingApplication::TAX
            ],
            'components' => [
                [
                    'net' => '100.00',
                    'gross' => '121.00',
                    'tax' => '21.00',
                    'taxRate' => '21.00',
                    'comment' => 'Test component'
                ]
            ]
        ]);
        $input = 'relmon-json://' . base64_encode($json);

        $dto = $parser->parse($input);
        
        $this->assertEquals('1.0', $dto->protocolIdentifier);
        $this->assertEquals('100.00', $dto->net);
        $this->assertEquals('121.00', $dto->gross);
        $this->assertEquals('21.00', $dto->tax);
        $this->assertEquals('21.00', $dto->taxRate);
        $this->assertEquals('EUR', $dto->unit);
        $this->assertEquals('2', $dto->precision);
        $this->assertEquals(Scope::ROOT, $dto->scope);
        $this->assertEquals(RoundingMode::UP, $dto->roundingMode);
        $this->assertEquals(RoundingApplication::TAX, $dto->roundingApplication);
        $this->assertCount(1, $dto->components);

        $component = $dto->components[0];
        $this->assertEquals('100.00', $component->getNet());
        $this->assertEquals('121.00', $component->getGross());
        $this->assertEquals('21.00', $component->getTax());
        $this->assertEquals('21.00', $component->getTaxRate());
        $this->assertEquals('Test component', $component->getComment());
    }
}
