<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\Dto\CanonicalMonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Tests\DummyMonetaryBasis;
use PHPUnit\Framework\TestCase;

class CanonicalMonetaryComponentDtoTest extends TestCase
{
    public function test_construct(): void
    {
        $basis = new DummyMonetaryBasis(100, 121, 21, 21, 2, 2);
        $dto = new CanonicalMonetaryComponentDto($basis, 'test comment');

        $this->assertSame(100, $dto->getNetInMinors());
        $this->assertSame(121, $dto->getGrossInMinors());
        $this->assertSame(21, $dto->getTaxInMinors());
        $this->assertSame(21, $dto->getTaxRateInMinors());
        $this->assertSame(2, $dto->getPrecision());
        $this->assertSame(2, $dto->getTaxRatePrecision());
        $this->assertSame('test comment', $dto->getComment());
    }
}
