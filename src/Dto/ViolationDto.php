<?php

namespace FromDevelopersForDevelopers\RelMon\Dto;

class ViolationDto
{
    public function __construct(private readonly string $message, private readonly string $field = '')
    {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getField(): string
    {
        return str_starts_with($this->field, '.') ? $this->field : ".{$this->field}";
    }
}
