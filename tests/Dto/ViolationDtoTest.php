<?php

namespace FromDevelopersForDevelopers\RelMon\Tests\Dto;

use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
use PHPUnit\Framework\TestCase;

class ViolationDtoTest extends TestCase
{
    public function test_construct(): void
    {
        $dto = new ViolationDto('Error message', 'field');
        $this->assertSame('Error message', $dto->getMessage());
        $this->assertSame('.field', $dto->getField());
    }

    public function test_get_field_with_dot(): void
    {
        $dto = new ViolationDto('Error message', '.field');
        $this->assertSame('.field', $dto->getField());
    }

    public function test_get_field_empty(): void
    {
        $dto = new ViolationDto('Error message', '');
        $this->assertSame('.', $dto->getField());
    }
}
