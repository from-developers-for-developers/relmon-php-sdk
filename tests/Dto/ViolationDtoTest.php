<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
use PHPUnit\Framework\TestCase;

class ViolationDtoTest extends TestCase
{
    public function testConstruct(): void
    {
        $dto = new ViolationDto('Error message', 'field');
        $this->assertSame('Error message', $dto->getMessage());
        $this->assertSame('.field', $dto->getField());
    }

    public function testGetFieldWithDot(): void
    {
        $dto = new ViolationDto('Error message', '.field');
        $this->assertSame('.field', $dto->getField());
    }

    public function testGetFieldEmpty(): void
    {
        $dto = new ViolationDto('Error message', '');
        $this->assertSame('.', $dto->getField());
    }
}
