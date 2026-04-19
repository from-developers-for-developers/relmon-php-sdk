<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Service;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\Service\MinorsService;
use FromDevelopersForDevelopers\RelMon\Tests\DummyMonetaryBasis;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MinorsServiceTest extends TestCase
{
    public static function toMinorsDataProvider(): array
    {
        return [
            [
                new RelMonDto('relmon@1.0.0/3', '100.00', '121.00', '21.00', '21.00'),
                2,
                2,
                new DummyMonetaryBasis(10000, 12100, 2100, 2100, 2, 2),
            ],
            [
                new RelMonDto('relmon@1.0.0/3', '20.40', '27.20', '6.80', '33.345'), // 20.40 * 100 = 2039.9999999999998
                2,
                3,
                new DummyMonetaryBasis(2040, 2720, 680, 33345, 2, 3),
            ],
        ];
    }

    #[DataProvider('toMinorsDataProvider')]
    public function test_toMinors(
        RelMonDto $dto,
        int $precision,
        int $taxRatePrecision,
        MonetaryBasisInterface $expectedBasis
    ): void
    {
        $service = new MinorsService();
        $resultBasis = $service->toMinors($dto, $precision, $taxRatePrecision);

        $this->assertSame($expectedBasis->getNetInMinors(), $resultBasis->getNetInMinors());
        $this->assertSame($expectedBasis->getGrossInMinors(), $resultBasis->getGrossInMinors());
        $this->assertSame($expectedBasis->getTaxInMinors(), $resultBasis->getTaxInMinors());
        $this->assertSame($expectedBasis->getTaxRateInMinors(), $resultBasis->getTaxRateInMinors());
        $this->assertSame($expectedBasis->getPrecision(), $resultBasis->getPrecision());
        $this->assertSame($expectedBasis->getTaxRatePrecision(), $resultBasis->getTaxRatePrecision());
    }
}
