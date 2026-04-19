<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Enum;

use FromDevelopersForDevelopers\RelMon\Enum\Format;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    public function test_values(): void
    {
        $values = Format::values();
        $this->assertGreaterThan(5, count($values));
        $this->assertContains(Format::AUTO, $values);
        $this->assertContains(Format::JSON_ARRAY, $values);
        $this->assertContains(Format::XML_STRING, $values);
    }

    public function test_tryFrom(): void
    {
        $this->assertSame(Format::AUTO, Format::tryFrom('auto'));
        $this->assertSame(Format::JSON_ARRAY, Format::tryFrom(Format::JSON_ARRAY));
        $this->assertNull(Format::tryFrom('invalid'));
    }
}
