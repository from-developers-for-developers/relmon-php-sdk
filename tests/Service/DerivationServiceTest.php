<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Service;

use FromDevelopersForDevelopers\RelMon\Dto\DerivedResultDto;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Exception\DerivationException;
use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\ProtocolIdentifier;
use FromDevelopersForDevelopers\RelMon\Service\DerivationService;
use FromDevelopersForDevelopers\RelMon\Tests\DummyMonetaryBasis;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DerivationServiceTest extends TestCase
{
    public static function deriveDataProvider(): array
    {
        return [
            // DL3
            [
                new DummyMonetaryBasis(),
                new ProtocolIdentifier('relmon@1.0.0/3'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Net, gross and tax must be specified for DL3.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000),
                new ProtocolIdentifier('relmon@1.0.0/3'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Net, gross and tax must be specified for DL3.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100),
                new ProtocolIdentifier('relmon@1.0.0/3'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Net, gross and tax must be specified for DL3.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 9999, taxInMinors: 2100, taxRateInMinors: 2100),
                new ProtocolIdentifier('relmon@1.0.0/3'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Gross must be greater then or equal to net.')
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100, taxInMinors: 2100, taxRateInMinors: 21000),
                new ProtocolIdentifier('relmon@1.0.0/3'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                3,
                new DerivedResultDto(10000, 12100, 2100, 21000),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100, taxInMinors: 2100, taxRateInMinors: 2101),
                new ProtocolIdentifier('relmon@1.0.0/3'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('The reconstruction of the tax amount has failed.'),
            ],

            // DL2
            [
                new DummyMonetaryBasis(),
                new ProtocolIdentifier('relmon@1.0.0/2'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Net, gross and tax rate must be specified for DL2.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000),
                new ProtocolIdentifier('relmon@1.0.0/2'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Net, gross and tax rate must be specified for DL2.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100),
                new ProtocolIdentifier('relmon@1.0.0/2'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Net, gross and tax rate must be specified for DL2.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 9999, taxRateInMinors: 2100),
                new ProtocolIdentifier('relmon@1.0.0/2'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Gross must be greater then or equal to net.')
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100, taxRateInMinors: 21000),
                new ProtocolIdentifier('relmon@1.0.0/2'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                3,
                new DerivedResultDto(10000, 12100, 2100, 21000),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12101, taxRateInMinors: 21000),
                new ProtocolIdentifier('relmon@1.0.0/2'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                3,
                null,
                new DerivationException('The reconstruction of the tax amount has failed.')
            ],

            // DL1
            [
                new DummyMonetaryBasis(netInMinors: 10000),
                new ProtocolIdentifier('relmon@1.0.0/1'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Tax rate must be specified for DL1.')
            ],
            [
                new DummyMonetaryBasis(taxRateInMinors: 2100),
                new ProtocolIdentifier('relmon@1.0.0/1'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Net or gross must be specified for DL1.')
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 9999, taxRateInMinors: 2100),
                new ProtocolIdentifier('relmon@1.0.0/1'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Gross must be greater then or equal to net.')
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12101, taxRateInMinors: 2100),
                new ProtocolIdentifier('relmon@1.0.0/1'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                null,
                new DerivationException('Calculated net/gross must be equal to the explicitly defined net/gross.')
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, taxRateInMinors: 2100),
                new ProtocolIdentifier('relmon@1.0.0/1'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                new DerivedResultDto(10000, 12100, 2100, 2100),
            ],
            [
                new DummyMonetaryBasis(grossInMinors: 12100, taxRateInMinors: 2100),
                new ProtocolIdentifier('relmon@1.0.0/1'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                2,
                new DerivedResultDto(10000, 12100, 2100, 2100),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, taxRateInMinors: 21000),
                new ProtocolIdentifier('relmon@1.0.0/1'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                3,
                new DerivedResultDto(10000, 12100, 2100, 21000),
            ],
            [
                new DummyMonetaryBasis(grossInMinors: 12100, taxRateInMinors: 21000),
                new ProtocolIdentifier('relmon@1.0.0/1'),
                RoundingModeEnum::HALF_EVEN,
                RoundingApplicationEnum::TAX,
                3,
                new DerivedResultDto(10000, 12100, 2100, 21000),
            ],

            // @TODO: add tests for different rounding modes/applications
        ];
    }

    #[DataProvider('deriveDataProvider')]
    public function test_derive(
        MonetaryBasisInterface  $basis,
        ProtocolIdentifier      $protocolIdentifier,
        RoundingModeEnum        $roundingMode,
        RoundingApplicationEnum $roundingApplication,
        ?int                    $taxRatePrecision,
        ?DerivedResultDto       $expectedResult,
        ?DerivationException    $exception = null
    )
    {
        $service = new DerivationService();

        if ($exception) {
            $this->expectException($exception::class);
            $this->expectExceptionMessage($exception->getMessage());
        }

        $result = $service->derive($basis, $protocolIdentifier, $roundingMode, $roundingApplication, $taxRatePrecision);

        if ($expectedResult) {
            $this->assertSame($expectedResult->getNet(), $result->getNet());
            $this->assertSame($expectedResult->getGross(), $result->getGross());
            $this->assertSame($expectedResult->getTax(), $result->getTax());
            $this->assertSame($expectedResult->getTaxRate(), $result->getTaxRate());
        }
    }
}
