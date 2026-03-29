<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class UriMinimalisticParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!str_starts_with($input, 'relmon-min://')) {
            throw new FormatParserWrongInputTypeException('Minimalistic URI notation is expected (relmon-min://...).');
        }

        $input = substr($input, 13);

        if (empty($input)) {
            throw new FormatParserWrongInputTypeException('Minimalistic URI notation is expected (relmon-min://...).');
        }

        $input = explode(';', $input);

        if (count($input) !== 4) {
            throw new FormatParserWrongInputTypeException('Minimalistic URI notation expects 4 parameters separated by semicolon.');
        }

        return new RelMonDto(protocolIdentifier: "relmon@{$input[0]}", net: $input[1], gross: $input[2], tax: $input[3]);
    }
}
