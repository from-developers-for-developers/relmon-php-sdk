<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Service;

use FromDevelopersForDevelopers\RelMon\Dto\CanonicalRelMonDto;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\DerivationException;
use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\Service\DerivationService;
use FromDevelopersForDevelopers\RelMon\Tests\DummyMonetaryBasis;
use FromDevelopersForDevelopers\RelMon\ValueObject\DerivedResult;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;
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
                'relmon@1.0.0/3',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                2,
                null,
                new DerivationException('Net + tax and/or gross + tax must be specified for DL3.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000),
                'relmon@1.0.0/3',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                2,
                null,
                new DerivationException('Net + tax and/or gross + tax must be specified for DL3.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100),
                'relmon@1.0.0/3',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                2,
                null,
                new DerivationException('Net + tax and/or gross + tax must be specified for DL3.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100, taxInMinors: 2100, taxRateInMinors: 21000),
                'relmon@1.0.0/3',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                3,
                new DerivedResult(10000, 12100, 2100, 2, 3, 21000),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100, taxInMinors: 2100, taxRateInMinors: 21010),
                'relmon@1.0.0/3',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                3,
                null,
                new DerivationException('The reconstruction of the tax amount has failed.'),
            ],

            // DL2
            [
                new DummyMonetaryBasis(),
                'relmon@1.0.0/2',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                2,
                null,
                new DerivationException('Net, gross and tax rate must be specified for DL2.'),
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, grossInMinors: 12100, taxRateInMinors: 21000),
                'relmon@1.0.0/2',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                3,
                new DerivedResult(10000, 12100, 2100, 2, 3, 21000),
            ],

            // DL1
            [
                new DummyMonetaryBasis(netInMinors: 10000),
                'relmon@1.0.0/1',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                2,
                null,
                new DerivationException('Tax rate must be specified for DL1.')
            ],
            [
                new DummyMonetaryBasis(netInMinors: 10000, taxRateInMinors: 2100),
                'relmon@1.0.0/1',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                2,
                new DerivedResult(10000, 12100, 2100, 2, 2, 2100),
            ],
            [
                new DummyMonetaryBasis(grossInMinors: 12100, taxRateInMinors: 2100),
                'relmon@1.0.0/1',
                RoundingMode::HALF_EVEN,
                RoundingApplication::TAX,
                2,
                new DerivedResult(10000, 12100, 2100, 2, 2, 2100),
            ],
        ];
    }

    #[DataProvider('deriveDataProvider')]
    public function test_derive(
        MonetaryBasisInterface  $basis,
        string                  $protocolIdentifier,
        string                  $roundingMode,
        string                  $roundingApplication,
        int                     $taxRatePrecision,
        ?DerivedResult          $expectedResult,
        ?\Exception             $exception = null
    )
    {
        $service = new DerivationService();
        $pi = new ProtocolIdentifier($protocolIdentifier);

        $relmon = new CanonicalRelMonDto(
            protocolIdentifier: $pi,
            scope: Scope::ROOT,
            roundingMode: $roundingMode,
            roundingApplication: $roundingApplication,
            basis: $basis,
            precision: 2,
            taxRatePrecision: $taxRatePrecision,
            unit: 'EUR'
        );

        if ($exception) {
            $this->expectException($exception::class);
            $this->expectExceptionMessage($exception->getMessage());
        }

        $result = $service->derive($relmon, $basis);

        if ($expectedResult) {
            $this->assertSame($expectedResult->getNetInMinors(), $result->getNetInMinors());
            $this->assertSame($expectedResult->getGrossInMinors(), $result->getGrossInMinors());
            $this->assertSame($expectedResult->getTaxInMinors(), $result->getTaxInMinors());
            $this->assertSame($expectedResult->getTaxRateInMinors(), $result->getTaxRateInMinors());
        }
    }
}
