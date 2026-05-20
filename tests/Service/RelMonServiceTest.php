<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Service;

use FromDevelopersForDevelopers\RelMon\Enum\Format;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\FormatNotSupportedException;
use FromDevelopersForDevelopers\RelMon\Exception\ValidationException;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserFactory;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserLocator;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonArrayParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\JsonStringParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriJsonParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriMinimalisticParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\UriXmlParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlDomDocumentParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlSimpleXmlParser;
use FromDevelopersForDevelopers\RelMon\FormatParser\XmlStringParser;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Service\DerivationService;
use FromDevelopersForDevelopers\RelMon\Service\MinorsService;
use FromDevelopersForDevelopers\RelMon\Service\RelMonService;
use FromDevelopersForDevelopers\RelMon\Service\ValidationService;
use PHPUnit\Framework\TestCase;

class RelMonServiceTest extends TestCase
{
    public static function buildAutoDetectDataProvider(): array
    {
        $jsonArray = [
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

        $jsonString = json_encode($jsonArray);
        $xmlString = <<<XML
<Relmon>
    <protocol>relmon@1.0.0/3</protocol>
    <net>100.00</net>
    <gross>121.00</gross>
    <tax>21.00</tax>
    <taxRate>21.00</taxRate>
    <unit>EUR</unit>
    <precision>2</precision>
    <scope>r</scope>
    <rounding>
        <mode>heven</mode>
        <application>tax</application>
    </rounding>
</Relmon>
XML;
        $simpleXml = new \SimpleXMLElement($xmlString);
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        $domDocument->loadXML($xmlString);

        return [
            'json array' => [$jsonArray, 'EUR', 2],
            'json string' => [$jsonString, 'EUR', 2],
            'xml string' => [$xmlString, 'EUR', 2],
            'simple xml' => [$simpleXml, 'EUR', 2],
            'dom document' => [$domDocument, 'EUR', 2],
            'json uri' => ['relmon-json://' . base64_encode($jsonString), 'EUR', 2],
            'xml uri' => ['relmon-xml://' . base64_encode($xmlString), 'EUR', 2],
            'minimalistic uri' => ['relmon-min://1.0.0/3;100.00;121.00;21.00', null, 2],
        ];
    }

    /**
     * @dataProvider buildAutoDetectDataProvider
     */
    public function testBuildAutoDetectsSupportedFormats(
        mixed $input,
        ?string $expectedUnit,
        int $expectedPrecision
    ): void {
        $relmon = $this->createService()->build($input);

        $this->assertSame(10000, $relmon->getNet());
        $this->assertSame(12100, $relmon->getGross());
        $this->assertSame(2100, $relmon->getTax());
        $this->assertSame($expectedUnit, $relmon->getUnit());
        $this->assertSame($expectedPrecision, $relmon->getPrecision());
        $this->assertSame(Scope::ROOT, $relmon->getScope());
        $this->assertSame(RoundingMode::HALF_EVEN, $relmon->getRoundingMode());
        $this->assertSame(RoundingApplication::TAX, $relmon->getRoundingApplication());
    }

    public function testBuildSupportsExplicitFormatOverride(): void
    {
        $input = json_encode([
            'protocol' => 'relmon@1.0.0/3',
            'net' => '100.00',
            'gross' => '121.00',
            'tax' => '21.00',
            'taxRate' => '21.00',
            'unit' => 'EUR',
            'precision' => 2,
        ]);

        $relmon = $this->createService()->build($input, Format::JSON_STRING);

        $this->assertSame(10000, $relmon->getNet());
        $this->assertSame(12100, $relmon->getGross());
        $this->assertSame(2100, $relmon->getTax());
    }

    public function testBuildWrapsInvalidProtocolIdentifierIntoValidationException(): void
    {
        try {
            $this->createService()->build([
                'protocol' => 'invalid',
                'net' => '100.00',
                'gross' => '121.00',
                'tax' => '21.00',
                'taxRate' => '21.00',
                'unit' => null,
                'u' => null,
                'precision' => null,
                'pr' => null,
            ]);
            $this->fail('ValidationException was expected.');
        } catch (ValidationException $exception) {
            $this->assertCount(1, $exception->getViolations());
            $this->assertSame(
                'ProtocolIdentifier should start with "relmon@".',
                $exception->getViolations()[0]->getMessage()
            );
            $this->assertSame('.protocolIdentifier', $exception->getViolations()[0]->getField());
        }
    }

    public function testBuildUsesDefaultScopeAndRoundingAndInfersPrecision(): void
    {
        $relmon = $this->createService()->build([
            'protocol' => 'relmon@1.0.0/1',
            'net' => '100.00',
            'taxRate' => '21.00',
            'gross' => null,
            'g' => null,
            'tax' => null,
            't' => null,
            'unit' => null,
            'u' => null,
            'precision' => null,
            'pr' => null,
        ]);

        $this->assertSame(10000, $relmon->getNet());
        $this->assertSame(12100, $relmon->getGross());
        $this->assertSame(2100, $relmon->getTax());
        $this->assertSame(2100, $relmon->getTaxRate());
        $this->assertSame(2, $relmon->getPrecision());
        $this->assertSame(Scope::ROOT, $relmon->getScope());
        $this->assertSame(RoundingMode::HALF_EVEN, $relmon->getRoundingMode());
        $this->assertSame(RoundingApplication::TAX, $relmon->getRoundingApplication());
    }

    public function testBuildAllowsIntegerTaxRateAndInfersPrecisionFromMaximumScale(): void
    {
        $relmon = $this->createService()->build([
            'protocol' => 'relmon@1.0.0/2',
            'net' => '100.0',
            'gross' => '121.00',
            'taxRate' => 21,
        ]);

        $this->assertSame(10000, $relmon->getNet());
        $this->assertSame(12100, $relmon->getGross());
        $this->assertSame(2100, $relmon->getTax());
        $this->assertSame(21, $relmon->getTaxRate());
        $this->assertSame(2, $relmon->getPrecision());
    }

    public function testBuildInfersPrecisionFromComponentsUsingMaximumScale(): void
    {
        $relmon = $this->createService()->build([
            'protocol' => 'relmon@1.0.0/3',
            'net' => '100.0',
            'gross' => '121.0',
            'tax' => '21.0',
            'scope' => 'c',
            'components' => [
                [
                    'net' => '40.00',
                    'gross' => '48.40',
                    'tax' => '8.40',
                    'taxRate' => 21,
                ],
                [
                    'net' => '60.00',
                    'gross' => '72.60',
                    'tax' => '12.60',
                    'taxRate' => 21,
                ],
            ],
        ]);

        $this->assertSame(2, $relmon->getPrecision());
        $this->assertSame(4000, $relmon->getComponents()[0]->getNet());
        $this->assertSame(4840, $relmon->getComponents()[0]->getGross());
        $this->assertSame(840, $relmon->getComponents()[0]->getTax());
        $this->assertSame(6000, $relmon->getComponents()[1]->getNet());
        $this->assertSame(7260, $relmon->getComponents()[1]->getGross());
        $this->assertSame(1260, $relmon->getComponents()[1]->getTax());
    }

    public function testBuildThrowsValidationExceptionForValidationViolations(): void
    {
        $this->expectException(ValidationException::class);

        $this->createService()->build([
            'protocol' => 'relmon@1.0.0/3',
            'net' => '121.00',
            'gross' => '100.00',
            'tax' => '21.00',
            'taxRate' => '21.00',
        ]);
    }

    public function testBuildDerivesComponentScopeComponents(): void
    {
        $relmon = $this->createService()->build([
            'protocol' => 'relmon@1.0.0/3',
            'net' => '100.00',
            'gross' => '121.00',
            'tax' => '21.00',
            'taxRate' => '21.00',
            'unit' => null,
            'u' => null,
            'precision' => null,
            'pr' => null,
            'scope' => 'c',
            'components' => [
                [
                    'net' => '40.00',
                    'gross' => '48.40',
                    'tax' => '8.40',
                    'taxRate' => '21.00',
                    'comment' => 'A',
                    'c' => 'A',
                ],
                [
                    'net' => '60.00',
                    'gross' => '72.60',
                    'tax' => '12.60',
                    'taxRate' => '21.00',
                    'comment' => 'B',
                    'c' => 'B',
                ],
            ],
        ]);

        $this->assertSame(Scope::COMPONENT, $relmon->getScope());
        $this->assertCount(2, $relmon->getComponents());
        $this->assertSame(4000, $relmon->getComponents()[0]->getNet());
        $this->assertSame(4840, $relmon->getComponents()[0]->getGross());
        $this->assertSame(840, $relmon->getComponents()[0]->getTax());
        $this->assertSame('A', $relmon->getComponents()[0]->getComment());
        $this->assertSame(6000, $relmon->getComponents()[1]->getNet());
        $this->assertSame(7260, $relmon->getComponents()[1]->getGross());
        $this->assertSame(1260, $relmon->getComponents()[1]->getTax());
        $this->assertSame('B', $relmon->getComponents()[1]->getComment());
    }

    public function testBuildThrowsValidationExceptionWhenComponentTotalsDoNotMatchRoot(): void
    {
        try {
            $this->createService()->build([
                'protocol' => 'relmon@1.0.0/3',
                'net' => '100.00',
                'gross' => '122.00',
                'tax' => '22.00',
                'taxRate' => '21.00',
                'unit' => null,
                'u' => null,
                'precision' => null,
                'pr' => null,
                'scope' => 'c',
                'components' => [
                    [
                        'net' => '40.00',
                        'gross' => '48.40',
                        'tax' => '8.40',
                        'taxRate' => '21.00',
                        'comment' => null,
                        'c' => null,
                    ],
                    [
                        'net' => '60.00',
                        'gross' => '72.60',
                        'tax' => '12.60',
                        'taxRate' => '21.00',
                        'comment' => null,
                        'c' => null,
                    ],
                ],
            ]);
            $this->fail('ValidationException was expected.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Gross amount does not match sum of component gross amounts.',
                $exception->getViolations()[0]->getMessage()
            );
            $this->assertSame('.gross', $exception->getViolations()[0]->getField());
        }
    }

    public function testBuildThrowsValidationExceptionWhenComponentNetTotalsDoNotMatchRoot(): void
    {
        try {
            $this->createService()->build([
                'protocol' => 'relmon@1.0.0/3',
                'net' => '101.00',
                'gross' => '121.00',
                'tax' => '20.00',
                'taxRate' => '21.00',
                'scope' => 'c',
                'components' => [
                    [
                        'net' => '40.00',
                        'gross' => '48.40',
                        'tax' => '8.40',
                        'taxRate' => '21.00',
                    ],
                    [
                        'net' => '60.00',
                        'gross' => '72.60',
                        'tax' => '12.60',
                        'taxRate' => '21.00',
                    ],
                ],
            ]);
            $this->fail('ValidationException was expected.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Net amount does not match sum of component net amounts.',
                $exception->getViolations()[0]->getMessage()
            );
            $this->assertSame('.net', $exception->getViolations()[0]->getField());
        }
    }

    public function testBuildThrowsFormatNotSupportedExceptionForUnknownInput(): void
    {
        $this->expectException(FormatNotSupportedException::class);
        $this->createService()->build(new \stdClass());
    }

    public function testGetPrecisionReturnsZeroForMinorsValues(): void
    {
        $service = $this->createService();
        $dto = new RelMonDto('relmon@1.0.0/3:m', null, 12100);

        $this->assertSame(0, $this->invokePrivateMethod($service, 'getPrecision', [$dto]));
    }

    public function testGetPrecisionReturnsZeroForWholeNumberStringsWithoutFraction(): void
    {
        $service = $this->createService();
        $dto = new RelMonDto('relmon@1.0.0/99', '100', null, null);

        $this->assertSame(0, $this->invokePrivateMethod($service, 'getPrecision', [$dto]));
    }

    public function testGetPrecisionReturnsMaximumScaleAcrossRootAndComponents(): void
    {
        $service = $this->createService();
        $dto = new RelMonDto(
            'relmon@1.0.0/99',
            '100.0',
            '121.00',
            '21.000',
            components: [new \FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto('10.00', '12.10', '2.10')]
        );

        $this->assertSame(3, $this->invokePrivateMethod($service, 'getPrecision', [$dto]));
    }

    public function testGetTaxRatePrecisionReturnsDefaultForWholeNumberTaxRate(): void
    {
        $service = $this->createService();
        $dto = new RelMonDto('relmon@1.0.0/99', taxRate: '21');

        $this->assertSame(3, $this->invokePrivateMethod($service, 'getTaxRatePrecision', [$dto, 3]));
    }

    private function createService(): RelMonService
    {
        $locator = new FormatParserLocator([
            new JsonArrayParser(),
            new JsonStringParser(),
            new UriJsonParser(),
            new UriMinimalisticParser(),
            new XmlSimpleXmlParser(),
            new XmlDomDocumentParser(),
            new XmlStringParser(),
            new UriXmlParser(),
        ]);

        return new RelMonService(
            new FormatParserFactory($locator),
            new ValidationService(),
            new MinorsService(),
            new DerivationService()
        );
    }

    private function invokePrivateMethod(object $object, string $methodName, array $args = []): mixed
    {
        $method = new \ReflectionMethod($object, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
