<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;

interface FormatParserInterface
{
    public function parse(mixed $input): RelMonDto;
}
