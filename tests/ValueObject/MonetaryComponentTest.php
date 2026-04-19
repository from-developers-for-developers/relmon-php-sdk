<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\ValueObject;

use FromDevelopersForDevelopers\RelMon\ValueObject\MonetaryComponent;
use PHPUnit\Framework\TestCase;

class MonetaryComponentTest extends TestCase
{
    public function test_construct(): void
    {
        $component = new MonetaryComponent(100, 121, 21, 21, 'comment');
        $this->assertSame(100, $component->getNet());
        $this->assertSame(121, $component->getGross());
        $this->assertSame(21, $component->getTax());
        $this->assertSame(21, $component->getTaxRate());
        $this->assertSame('comment', $component->getComment());
    }
}
