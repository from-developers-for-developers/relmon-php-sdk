<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\ValueObject\DerivedResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DerivedResultTest extends TestCase
{
    public static function constructDataProvider(): array
    {
        return [
            [10000, 12100, 2100, 2, 2, 2100],
            [10000, 12100, 2100, 2, 2, null],
        ];
    }

    #[DataProvider('constructDataProvider')]
    public function test_construct(int $net, int $gross, int $tax, int $precision, int $taxRatePrecision, ?int $taxRate = null)
    {
        $dto = new DerivedResult($net, $gross, $tax, $precision, $taxRatePrecision, $taxRate);

        $this->assertSame($net, $dto->getNetInMinors());
        $this->assertSame($gross, $dto->getGrossInMinors());
        $this->assertSame($tax, $dto->getTaxInMinors());
        $this->assertSame($taxRate, $dto->getTaxRateInMinors());
        $this->assertSame($precision, $dto->getPrecision());
        $this->assertSame($taxRatePrecision, $dto->getTaxRatePrecision());
    }
}
