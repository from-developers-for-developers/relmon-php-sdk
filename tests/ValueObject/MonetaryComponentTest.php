<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\ValueObject;

use FromDevelopersForDevelopers\RelMon\ValueObject\MonetaryComponent;
use PHPUnit\Framework\TestCase;

class MonetaryComponentTest extends TestCase
{
    public function testConstruct(): void
    {
        $component = new MonetaryComponent(100, 121, 21, 21, 'comment', 2, 1);
        $this->assertSame(100, $component->getNet());
        $this->assertSame(121, $component->getGross());
        $this->assertSame(21, $component->getTax());
        $this->assertSame(21, $component->getTaxRate());
        $this->assertSame('comment', $component->getComment());
    }

    public function testFormattedValues(): void
    {
        $component = new MonetaryComponent(-123456, -149381, -25925, 2100, 'comment', 2, 2);

        $this->assertSame('-1234.56', $component->getNetFormatted());
        $this->assertSame('-1493.81', $component->getGrossFormatted());
        $this->assertSame('-259.25', $component->getTaxFormatted());
        $this->assertSame('21.00', $component->getTaxRateFormatted());
        $this->assertSame('-1 234,56', $component->getNetFormatted(',', ' '));
    }
}
