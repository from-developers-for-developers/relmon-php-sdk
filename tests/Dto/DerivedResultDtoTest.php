<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\Dto\DerivedResultDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DerivedResultDtoTest extends TestCase
{
    public static function constructDataProvider(): array
    {
        return [
            [10000, 12100, 2100, 2100],
            [10000, 12100, 2100],
        ];
    }

    #[DataProvider('constructDataProvider')]
    public function test_construct(int $net, int $gross, int $tax, ?int $taxRate = null)
    {
        $dto = new DerivedResultDto($net, $gross, $tax, $taxRate);

        $this->assertSame($net, $dto->getNet());
        $this->assertSame($gross, $dto->getGross());
        $this->assertSame($tax, $dto->getTax());
        $this->assertSame($taxRate, $dto->getTaxRate());
    }
}
