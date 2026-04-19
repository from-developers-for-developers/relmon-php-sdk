<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\FormatParser;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\ValidationException;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonArrayParser;
use PHPUnit\Framework\TestCase;

class JsonArrayParserTest extends TestCase
{
    private JsonArrayParser $parser;

    protected function setUp(): void
    {
        $this->parser = new JsonArrayParser();
    }

    public function testParseValidArray(): void
    {
        $input = [
            'protocol' => '1.0',
            'net' => '100.00',
            'gross' => '121.00',
            'tax' => '21.00',
            'taxRate' => '21.00',
            'unit' => 'EUR',
            'precision' => '2',
            'scope' => Scope::ROOT,
            'rounding' => [
                'mode' => 'up',
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
        ];

        $dto = $this->parser->parse($input);

        $this->assertEquals('1.0', $dto->protocolIdentifier);
        $this->assertEquals('100.00', $dto->net);
        $this->assertEquals('121.00', $dto->gross);
        $this->assertEquals('21.00', $dto->tax);
        $this->assertEquals('21.00', $dto->taxRate);
        $this->assertEquals('EUR', $dto->unit);
        $this->assertEquals('2', $dto->precision);
        $this->assertEquals(Scope::ROOT, $dto->scope);
        $this->assertEquals('up', $dto->roundingMode);
        $this->assertEquals(RoundingApplication::TAX, $dto->roundingApplication);
        $this->assertCount(1, $dto->components);

        $component = $dto->components[0];
        $this->assertEquals('100.00', $component->getNet());
        $this->assertEquals('121.00', $component->getGross());
        $this->assertEquals('21.00', $component->getTax());
        $this->assertEquals('21.00', $component->getTaxRate());
        $this->assertEquals('Test component', $component->getComment());
    }

    public function testParseCompactNotationArray(): void
    {
        $input = [
            'p' => '1.0',
            'n' => '100.00',
            'g' => '121.00',
            't' => '21.00',
            'tr' => '21.00',
            'u' => 'EUR',
            'pr' => '2',
            's' => Scope::COMPONENT,
            'r' => [
                'm' => 'up',
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
        ];

        $dto = $this->parser->parse($input);

        $this->assertEquals('1.0', $dto->protocolIdentifier);
        $this->assertEquals('100.00', $dto->net);
        $this->assertEquals('121.00', $dto->gross);
        $this->assertEquals('21.00', $dto->tax);
        $this->assertEquals('21.00', $dto->taxRate);
        $this->assertEquals('EUR', $dto->unit);
        $this->assertEquals('2', $dto->precision);
        $this->assertEquals(Scope::COMPONENT, $dto->scope);
        $this->assertEquals('up', $dto->roundingMode);
        $this->assertEquals(RoundingApplication::TAX, $dto->roundingApplication);
        $this->assertCount(1, $dto->components);

        $component = $dto->components[0];
        $this->assertEquals('100.00', $component->getNet());
        $this->assertEquals('121.00', $component->getGross());
        $this->assertEquals('21.00', $component->getTax());
        $this->assertEquals('21.00', $component->getTaxRate());
        $this->assertEquals('Test component', $component->getComment());
    }

    public function testInvalidRoundingModeThrowsException(): void
    {
        $input = [
            'rounding' => ['mode' => 'invalid', 'application' => RoundingApplication::TAX]
        ];

        $this->expectException(ValidationException::class);
        $this->parser->parse($input);
    }

    public function testInvalidRoundingApplicationThrowsException(): void
    {
        $input = [
            'rounding' => ['mode' => RoundingMode::UP, 'application' => 'invalid']
        ];

        $this->expectException(ValidationException::class);
        $this->parser->parse($input);
    }

    public function testInvalidScopeThrowsException(): void
    {
        $input = ['scope' => 'invalid'];

        $this->expectException(ValidationException::class);
        $this->parser->parse($input);
    }
}
