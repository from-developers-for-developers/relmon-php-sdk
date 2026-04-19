<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Enum;

use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    public function test_values(): void
    {
        $values = Scope::values();
        $this->assertCount(2, $values);
        $this->assertContains(Scope::ROOT, $values);
        $this->assertContains(Scope::COMPONENT, $values);
    }

    public function test_tryFrom(): void
    {
        $this->assertSame(Scope::ROOT, Scope::tryFrom('r'));
        $this->assertSame(Scope::COMPONENT, Scope::tryFrom('c'));
        $this->assertNull(Scope::tryFrom('invalid'));
    }
}
