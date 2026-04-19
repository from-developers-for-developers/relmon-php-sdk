<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Exception;

use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;
use FromDevelopersForDevelopers\RelMon\Exception\RelMonException;
use PHPUnit\Framework\TestCase;

class FormatParserWrongInputTypeExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->assertInstanceOf(RelMonException::class, new FormatParserWrongInputTypeException());
    }
}
