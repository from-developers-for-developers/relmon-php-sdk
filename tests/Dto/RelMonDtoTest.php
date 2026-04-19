<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use PHPUnit\Framework\TestCase;

class RelMonDtoTest extends TestCase
{
    public function test_construct(): void
    {
        $components = [new MonetaryComponentDto(100, 121, 21, 21)];
        $dto = new RelMonDto(
            protocolIdentifier: 'relmon@1.0.0/3',
            net: 100,
            gross: 121,
            tax: 21,
            taxRate: 21,
            unit: 'EUR',
            precision: 2,
            scope: 'r',
            roundingMode: 'heven',
            roundingApplication: 'tax',
            components: $components
        );

        $this->assertSame('relmon@1.0.0/3', $dto->protocolIdentifier);
        $this->assertSame(100, $dto->getNet());
        $this->assertSame(121, $dto->getGross());
        $this->assertSame(21, $dto->getTax());
        $this->assertSame(21, $dto->getTaxRate());
        $this->assertSame('EUR', $dto->unit);
        $this->assertSame(2, $dto->precision);
        $this->assertSame('r', $dto->scope);
        $this->assertSame('heven', $dto->roundingMode);
        $this->assertSame('tax', $dto->roundingApplication);
        $this->assertSame($components, $dto->components);
    }
}
