<?php

namespace FromDevelopersForDevelopers\RelMon\Dto;

class DerivedResultDto
{
    public function __construct(
        public readonly int $net,
        public readonly int $gross,
        public readonly int $tax,
        public readonly ?int $taxRate,
    )
    {
    }
}
