<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\Dto\CanonicalMonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\CanonicalRelMonDto;
use FromDevelopersForDevelopers\RelMon\Tests\DummyMonetaryBasis;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;
use PHPUnit\Framework\TestCase;

class CanonicalRelMonDtoTest extends TestCase
{
    public function test_construct(): void
    {
        $pi = new ProtocolIdentifier('relmon@1.0.0/3');
        $basis = new DummyMonetaryBasis(1000, 1210, 210, 210, 2, 2);
        $components = [new CanonicalMonetaryComponentDto($basis, 'test')];
        
        $dto = new CanonicalRelMonDto(
            protocolIdentifier: $pi,
            scope: 'r',
            roundingMode: 'heven',
            roundingApplication: 'tax',
            basis: $basis,
            precision: 2,
            taxRatePrecision: 2,
            unit: 'EUR',
            components: $components
        );

        $this->assertSame($pi, $dto->getProtocolIdentifier());
        $this->assertSame('r', $dto->getScope());
        $this->assertSame('heven', $dto->getRoundingMode());
        $this->assertSame('tax', $dto->getRoundingApplication());
        $this->assertSame($basis, $dto->getBasis());
        $this->assertSame(2, $dto->getPrecision());
        $this->assertSame(2, $dto->getTaxRatePrecision());
        $this->assertSame('EUR', $dto->getUnit());
        $this->assertSame($components, $dto->getComponents());
        
        $this->assertSame(1000, $dto->getNetInMinors());
        $this->assertSame(1210, $dto->getGrossInMinors());
        $this->assertSame(210, $dto->getTaxInMinors());
        $this->assertSame(210, $dto->getTaxRateInMinors());
    }
}
