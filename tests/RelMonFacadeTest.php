<?php

namespace FromDevelopersForDevelopers\RelMon\Tests;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Enum\Format;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserInterface;
use FromDevelopersForDevelopers\RelMon\RelMonFacade;
use PHPUnit\Framework\TestCase;

class RelMonFacadeTest extends TestCase
{
    public static function buildDataProvider(): array
    {
        $input = [
            'protocol' => 'relmon@1.0.0/3',
            'net' => '100.00',
            'gross' => '121.00',
            'tax' => '21.00',
            'taxRate' => '21.00',
            'unit' => 'EUR',
            'precision' => 2,
            'scope' => 'r',
            'rounding' => [
                'mode' => 'heven',
                'application' => 'tax',
            ],
        ];

        return [
            'json array' => [$input, Format::AUTO],
            'json string' => [json_encode($input), Format::JSON_STRING],
        ];
    }

    /**
     * @dataProvider buildDataProvider
     */
    public function testBuildWorksWithBuiltInDefaults(mixed $input, string $format): void
    {
        $relmon = RelMonFacade::build($input, $format);

        $this->assertSame(10000, $relmon->getNet());
        $this->assertSame(12100, $relmon->getGross());
        $this->assertSame(2100, $relmon->getTax());
        $this->assertSame('EUR', $relmon->getUnit());
        $this->assertSame(2, $relmon->getPrecision());
        $this->assertSame(Scope::ROOT, $relmon->getScope());
        $this->assertSame(RoundingMode::HALF_EVEN, $relmon->getRoundingMode());
        $this->assertSame(RoundingApplication::TAX, $relmon->getRoundingApplication());
    }

    public function testBuildSupportsCustomFormatParsers(): void
    {
        $parser = new class implements FormatParserInterface {
            public function parse(mixed $input): RelMonDto
            {
                [$protocol, $net, $gross, $tax, $taxRate] = str_getcsv($input);

                return new RelMonDto(
                    protocolIdentifier: $protocol,
                    net: $net,
                    gross: $gross,
                    tax: $tax,
                    taxRate: $taxRate,
                );
            }
        };

        $relmon = RelMonFacade::build(
            'relmon@1.0.0/3,100.00,121.00,21.00,21.00',
            get_class($parser),
            [$parser],
        );

        $this->assertSame(10000, $relmon->getNet());
        $this->assertSame(12100, $relmon->getGross());
        $this->assertSame(2100, $relmon->getTax());
        $this->assertSame(2100, $relmon->getTaxRate());
    }

    public function testBuildSupportsDefaults(): void
    {
        $relmon = RelMonFacade::build(
            [
                'protocol' => 'relmon@1.0.0/1',
                'net' => '100.00',
            ],
            Format::AUTO,
            [],
            [
                'unit' => 'EUR',
                'taxRate' => '21.00',
            ],
        );

        $this->assertSame(12100, $relmon->getGross());
        $this->assertSame(2100, $relmon->getTax());
        $this->assertSame(2100, $relmon->getTaxRate());
        $this->assertSame('EUR', $relmon->getUnit());
    }
}
