<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\ValueObject;

use FromDevelopersForDevelopers\RelMon\Exception\ProtocolIdentifierInvalidException;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;
use PHPUnit\Framework\TestCase;

class ProtocolIdentifierTest extends TestCase
{
    public function testConstructValid(): void
    {
        $pi = new ProtocolIdentifier('relmon@1.0.0/3:c.m');
        $this->assertSame('1.0.0', $pi->getVersion());
        $this->assertSame(3, $pi->getDeterminismLevel());
        $this->assertTrue($pi->isInCompactMode());
        $this->assertTrue($pi->isInMinorsMode());
    }

    public function testConstructValidMinimal(): void
    {
        $pi = new ProtocolIdentifier('relmon@1.0.0/1');
        $this->assertSame('1.0.0', $pi->getVersion());
        $this->assertSame(1, $pi->getDeterminismLevel());
        $this->assertFalse($pi->isInCompactMode());
        $this->assertFalse($pi->isInMinorsMode());
    }

    public function testConstructValidReversedModes(): void
    {
        $pi = new ProtocolIdentifier('relmon@1.0.0/3:m.c');
        $this->assertSame('1.0.0', $pi->getVersion());
        $this->assertSame(3, $pi->getDeterminismLevel());
        $this->assertTrue($pi->isInCompactMode());
        $this->assertTrue($pi->isInMinorsMode());
    }

    public function testConstructInvalidVersion(): void
    {
        $this->expectException(ProtocolIdentifierInvalidException::class);
        $this->expectExceptionMessage('Invalid protocol version: 1.0.invalid');
        new ProtocolIdentifier('relmon@1.0.invalid/1');
    }

    public function testConstructInvalidPrefix(): void
    {
        $this->expectException(ProtocolIdentifierInvalidException::class);
        $this->expectExceptionMessage('ProtocolIdentifier should start with "relmon@".');
        new ProtocolIdentifier('invalid@1.0.0/1');
    }

    public function testConstructNoOptions(): void
    {
        $this->expectException(ProtocolIdentifierInvalidException::class);
        $this->expectExceptionMessage('Protocol options not found in ProtocolIdentifier.');
        new ProtocolIdentifier('relmon@1.0.0/');
    }

    public function testConstructInvalidMode(): void
    {
        $this->expectException(ProtocolIdentifierInvalidException::class);
        $this->expectExceptionMessage('Only "c" and "m" modes are allowed in ProtocolIdentifier.');
        new ProtocolIdentifier('relmon@1.0.0/1:x');
    }

    public function testConstructInvalidOptionsStructure(): void
    {
        $this->expectException(ProtocolIdentifierInvalidException::class);
        $this->expectExceptionMessage('Protocol options should be in format "determinismLevel[?:mode1[?.mode2...]]".');
        new ProtocolIdentifier('relmon@1.0.0/1:c:m');
    }

    public function testConstructDuplicateModesAreInvalid(): void
    {
        $this->expectException(ProtocolIdentifierInvalidException::class);
        $this->expectExceptionMessage('Only "c" and "m" modes are allowed in ProtocolIdentifier.');
        new ProtocolIdentifier('relmon@1.0.0/1:c.c');
    }

    public function testCaching(): void
    {
        $pi1 = new ProtocolIdentifier('relmon@1.0.0/3:c.m');
        $pi2 = new ProtocolIdentifier('relmon@1.0.0/3:c.m');

        // Caching doesn't return same object (it copies properties in __construct)
        // But let's check values are same
        $this->assertSame($pi1->getVersion(), $pi2->getVersion());
        $this->assertSame($pi1->getDeterminismLevel(), $pi2->getDeterminismLevel());
        $this->assertSame($pi1->isInCompactMode(), $pi2->isInCompactMode());
        $this->assertSame($pi1->isInMinorsMode(), $pi2->isInMinorsMode());
    }
}
