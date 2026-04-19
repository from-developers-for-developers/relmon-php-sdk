<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Exception;

use FromDevelopersForDevelopers\RelMon\Exception\FormatNotSupportedException;
use FromDevelopersForDevelopers\RelMon\Exception\RelMonException;
use PHPUnit\Framework\TestCase;

class FormatNotSupportedExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->assertInstanceOf(RelMonException::class, new FormatNotSupportedException());
    }
}
