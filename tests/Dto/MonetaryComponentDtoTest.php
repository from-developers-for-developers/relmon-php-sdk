<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Tests\DummyMonetaryBasis;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MonetaryComponentDtoTest extends TestCase
{
    public static function constructDataProvider(): array
    {
        return [
            [10000, 12100, 2100, 2100, 'test'],
            [10000, 12100, 2100],
            [null, null, null]
        ];
    }

    #[DataProvider('constructDataProvider')]
    public function test_construct(?int $net, ?int $gross, ?int $tax, ?int $taxRate = null, ?string $comment = null)
    {
        $dto = new MonetaryComponentDto($net, $gross, $tax, $taxRate, $comment);

        $this->assertSame($net, $dto->getNet());
        $this->assertSame($gross, $dto->getGross());
        $this->assertSame($tax, $dto->getTax());
        $this->assertSame($taxRate, $dto->getTaxRate());
        $this->assertSame($comment, $dto->getComment());
        $this->assertNull($dto->getNetInMinors());
        $this->assertNull($dto->getGrossInMinors());
        $this->assertNull($dto->getTaxInMinors());
        $this->assertNull($dto->getTaxRateInMinors());
    }

    public function test_setMinors(): void
    {
        $dto = new MonetaryComponentDto('100.00', '121.00', '21.00', '21.00');
        $dto->setMinors(new DummyMonetaryBasis(
            netInMinors: 10000,
            grossInMinors: 12100,
            taxInMinors: 2100,
            taxRateInMinors: 2100
        ));

        $this->assertSame(10000, $dto->getNetInMinors());
        $this->assertSame(12100, $dto->getGrossInMinors());
        $this->assertSame(2100, $dto->getTaxInMinors());
        $this->assertSame(2100, $dto->getTaxRateInMinors());
    }
}
