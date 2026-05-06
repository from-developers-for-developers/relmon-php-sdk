<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class UriXmlParser extends XmlStringParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!is_string($input)) {
            throw new FormatParserWrongInputTypeException('XML URI notation is expected (relmon-xml://...).');
        }

        if (!str_starts_with($input, 'relmon-xml://')) {
            throw new FormatParserWrongInputTypeException('XML URI notation is expected (relmon-xml://...).');
        }

        $input = substr($input, 13);

        if (empty($input)) {
            throw new FormatParserWrongInputTypeException('XML URI notation is expected (relmon-xml://...).');
        }

        $input = strtr($input, '-_', '+/');
        $padding = strlen($input) % 4;

        if ($padding !== 0) {
            $input .= str_repeat('=', 4 - $padding);
        }

        $input = base64_decode($input, true);

        if ($input === false) {
            throw new FormatParserWrongInputTypeException('Could not decode XML URI.');
        }

        return parent::parse($input);
    }
}
