<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\ValueObject;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\ValueObject\MonetaryComponent;
use FromDevelopersForDevelopers\RelMon\ValueObject\RelMonObject;
use PHPUnit\Framework\TestCase;

class RelMonObjectTest extends TestCase
{
    public function test_construct(): void
    {
        $components = [new MonetaryComponent(100, 121, 21, 21, 'test')];
        $relmon = new RelMonObject(
            net: 1000,
            gross: 1210,
            tax: 210,
            taxRate: 210,
            unit: 'EUR',
            precision: 2,
            scope: Scope::COMPONENT,
            roundingMode: RoundingMode::HALF_AWAY_FROM_ZERO,
            roundingApplication: RoundingApplication::TOTAL,
            components: $components
        );

        $this->assertSame(1000, $relmon->getNet());
        $this->assertSame(1210, $relmon->getGross());
        $this->assertSame(210, $relmon->getTax());
        $this->assertSame(210, $relmon->getTaxRate());
        $this->assertSame('EUR', $relmon->getUnit());
        $this->assertSame(2, $relmon->getPrecision());
        $this->assertSame(Scope::COMPONENT, $relmon->getScope());
        $this->assertSame(RoundingMode::HALF_AWAY_FROM_ZERO, $relmon->getRoundingMode());
        $this->assertSame(RoundingApplication::TOTAL, $relmon->getRoundingApplication());
        $this->assertSame($components, $relmon->getComponents());
    }

    public function test_construct_default(): void
    {
        $relmon = new RelMonObject(1000, 1210, 210);
        
        $this->assertSame(1000, $relmon->getNet());
        $this->assertSame(Scope::ROOT, $relmon->getScope());
        $this->assertSame(RoundingMode::HALF_EVEN, $relmon->getRoundingMode());
        $this->assertSame(RoundingApplication::TAX, $relmon->getRoundingApplication());
        $this->assertEmpty($relmon->getComponents());
    }
}
