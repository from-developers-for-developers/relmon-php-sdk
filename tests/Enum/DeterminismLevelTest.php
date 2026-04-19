<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Enum;

use FromDevelopersForDevelopers\RelMon\Enum\DeterminismLevel;
use PHPUnit\Framework\TestCase;

class DeterminismLevelTest extends TestCase
{
    public function test_values(): void
    {
        $values = DeterminismLevel::values();
        $this->assertCount(3, $values);
        $this->assertContains(DeterminismLevel::DL1, $values);
        $this->assertContains(DeterminismLevel::DL2, $values);
        $this->assertContains(DeterminismLevel::DL3, $values);
    }

    public function test_tryFrom(): void
    {
        $this->assertSame(DeterminismLevel::DL1, DeterminismLevel::tryFrom(1));
        $this->assertSame(DeterminismLevel::DL2, DeterminismLevel::tryFrom(2));
        $this->assertSame(DeterminismLevel::DL3, DeterminismLevel::tryFrom(3));
        $this->assertNull(DeterminismLevel::tryFrom(4));
    }
}
