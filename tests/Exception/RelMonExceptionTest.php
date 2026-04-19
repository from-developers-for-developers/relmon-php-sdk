<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Exception;

use FromDevelopersForDevelopers\RelMon\Exception\RelMonException;
use PHPUnit\Framework\TestCase;

class RelMonExceptionTest extends TestCase
{
    public function testException(): void
    {
        $this->assertInstanceOf(\Exception::class, new RelMonException());
    }
}
