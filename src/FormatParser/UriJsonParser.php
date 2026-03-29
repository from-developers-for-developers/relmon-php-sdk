<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class UriJsonParser extends JsonStringParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!str_starts_with($input, 'relmon-json://')) {
            throw new FormatParserWrongInputTypeException('JSON URI notation is expected (relmon-json://...).');
        }

        $input = substr($input, 14);

        if (empty($input)) {
            throw new FormatParserWrongInputTypeException('JSON URI notation is expected (relmon-json://...).');
        }

        $input = base64_decode($input);

        if ($input === false) {
            throw new FormatParserWrongInputTypeException('Could not decode JSON URI.');
        }

        return parent::parse($input);
    }
}
