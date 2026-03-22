<?php

namespace RelMon\FormatParser;

use RelMon\Dto\RelMonDto;

interface FormatParserInterface
{
    public function parse(mixed $input): RelMonDto;
}
