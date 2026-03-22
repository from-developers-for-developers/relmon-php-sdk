<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Service;

use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;
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
                new DummyMonetaryBasis('100.00', '121.00', '21.00', '21.00'),
                2,
                null, // no tax rate precision given
                new DummyMonetaryBasis('100.00', '121.00', '21.00', '21.00', 10000, 12100, 2100, null), // null because no tax rate precision
            ],
            [
                new DummyMonetaryBasis('100.00', '121.00', '21.00', '21.00'),
                2,
                2,
                new DummyMonetaryBasis('100.00', '121.00', '21.00', '21.00', 10000, 12100, 2100, 2100),
            ],
            [
                new DummyMonetaryBasis('20.40', '27.20', '6.80', '33.345'), // 20.40 * 100 = 2039.9999999999998
                2,
                3,
                new DummyMonetaryBasis('20.40', '27.20', '6.80', '33.345', 2040, 2720, 680, 33345),
            ],
        ];
    }

    #[DataProvider('toMinorsDataProvider')]
    public function test_toMinors(
        MonetaryBasisInterface $basis,
        int $precision,
        ?int $taxRatePrecision,
        MonetaryBasisInterface $expectedBasis
    ): void
    {
        $service = new MinorsService();
        $resultBasis = $service->toMinors($basis, $precision, $taxRatePrecision);

        $this->assertSame($expectedBasis->getNet(), $resultBasis->getNet());
        $this->assertSame($expectedBasis->getGross(), $resultBasis->getGross());
        $this->assertSame($expectedBasis->getTax(), $resultBasis->getTax());
        $this->assertSame($expectedBasis->getTaxRate(), $resultBasis->getTaxRate());
        $this->assertSame($expectedBasis->getNetInMinors(), $resultBasis->getNetInMinors());
        $this->assertSame($expectedBasis->getGrossInMinors(), $resultBasis->getGrossInMinors());
        $this->assertSame($expectedBasis->getTaxInMinors(), $resultBasis->getTaxInMinors());
        $this->assertSame($expectedBasis->getTaxRateInMinors(), $resultBasis->getTaxRateInMinors());
    }
}
