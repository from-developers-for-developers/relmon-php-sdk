<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
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

    /**
     * @dataProvider constructDataProvider
     */
    public function test_construct(?int $net, ?int $gross, ?int $tax, ?int $taxRate = null, ?string $comment = null)
    {
        $dto = new MonetaryComponentDto($net, $gross, $tax, $taxRate, $comment);

        $this->assertSame($net, $dto->getNet());
        $this->assertSame($gross, $dto->getGross());
        $this->assertSame($tax, $dto->getTax());
        $this->assertSame($taxRate, $dto->getTaxRate());
        $this->assertSame($comment, $dto->getComment());
    }
}
