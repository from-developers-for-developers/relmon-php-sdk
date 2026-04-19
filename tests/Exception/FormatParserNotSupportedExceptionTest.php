<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Exception;

use FromDevelopersForDevelopers\RelMon\Exception\FormatParserNotSupportedException;
use FromDevelopersForDevelopers\RelMon\Exception\RelMonException;
use PHPUnit\Framework\TestCase;

class FormatParserNotSupportedExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->assertInstanceOf(RelMonException::class, new FormatParserNotSupportedException());
    }
}
