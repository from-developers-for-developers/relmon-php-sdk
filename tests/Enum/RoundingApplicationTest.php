<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Enum;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use PHPUnit\Framework\TestCase;

class RoundingApplicationTest extends TestCase
{
    public function test_values(): void
    {
        $values = RoundingApplication::values();
        $this->assertCount(2, $values);
        $this->assertContains(RoundingApplication::TAX, $values);
        $this->assertContains(RoundingApplication::TOTAL, $values);
    }

    public function test_tryFrom(): void
    {
        $this->assertSame(RoundingApplication::TAX, RoundingApplication::tryFrom('tax'));
        $this->assertSame(RoundingApplication::TOTAL, RoundingApplication::tryFrom('total'));
        $this->assertNull(RoundingApplication::tryFrom('invalid'));
    }
}
