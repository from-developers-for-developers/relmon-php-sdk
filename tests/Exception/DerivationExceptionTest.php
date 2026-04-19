<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Exception;

use FromDevelopersForDevelopers\RelMon\Exception\DerivationException;
use FromDevelopersForDevelopers\RelMon\Exception\RelMonException;
use PHPUnit\Framework\TestCase;

class DerivationExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->assertInstanceOf(RelMonException::class, new DerivationException());
    }
}
