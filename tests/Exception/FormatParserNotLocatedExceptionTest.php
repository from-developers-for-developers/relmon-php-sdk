<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Exception;

use FromDevelopersForDevelopers\RelMon\Exception\FormatParserNotLocatedException;
use FromDevelopersForDevelopers\RelMon\Exception\RelMonException;
use PHPUnit\Framework\TestCase;

class FormatParserNotLocatedExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->assertInstanceOf(RelMonException::class, new FormatParserNotLocatedException());
    }
}
