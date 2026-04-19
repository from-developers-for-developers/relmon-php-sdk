<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Exception;

use FromDevelopersForDevelopers\RelMon\Exception\RelMonException;
use FromDevelopersForDevelopers\RelMon\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new ValidationException([]);
        $this->assertInstanceOf(RelMonException::class, $exception);
        $this->assertIsArray($exception->getViolations());
    }
}
