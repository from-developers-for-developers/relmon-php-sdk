<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class JsonStringParser extends JsonArrayParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!str_starts_with($input, '{')) {
            throw new FormatParserWrongInputTypeException('JSON string notation is expected.');
        }

        $input = json_decode($input, true);

        if (!is_array($input)) {
            throw new FormatParserWrongInputTypeException('Could not parse JSON string.');
        }

        return parent::parse($input);
    }
}
