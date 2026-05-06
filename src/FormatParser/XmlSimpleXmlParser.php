<?php

namespace FromDevelopersForDevelopers\RelMon\FormatParser;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Exception\FormatParserWrongInputTypeException;

class XmlSimpleXmlParser extends JsonArrayParser implements FormatParserInterface
{
    public function parse(mixed $input): RelMonDto
    {
        if (!$input instanceof \SimpleXMLElement) {
            throw new FormatParserWrongInputTypeException('SimpleXMLElement instance is expected.');
        }

        return parent::parse($this->toCanonicalArray($input));
    }

    private function toCanonicalArray(\SimpleXMLElement $input): array
    {
        $data = [];
        $this->setAliasedXmlValue($data, $input, 'protocol', 'p');
        $this->setAliasedXmlValue($data, $input, 'net', 'n');
        $this->setAliasedXmlValue($data, $input, 'gross', 'g');
        $this->setAliasedXmlValue($data, $input, 'tax', 't');
        $this->setAliasedXmlValue($data, $input, 'taxRate', 'tr');
        $this->setAliasedXmlValue($data, $input, 'unit', 'u');
        $this->setAliasedXmlValue($data, $input, 'precision', 'pr', $this->getPrecisionValue($input));
        $this->setAliasedXmlValue($data, $input, 'scope', 's');

        $rounding = $this->extractRounding($input);
        if ($rounding !== []) {
            $data['rounding'] = $rounding;
        }

        $components = $this->extractComponents($input);
        if ($components !== []) {
            $data['components'] = $components;
        }

        return $data;
    }

    private function extractRounding(\SimpleXMLElement $input): array
    {
        $roundingNode = $this->getFirstChild($input, 'rounding', 'r');

        if ($roundingNode === null) {
            return [];
        }

        $rounding = [];
        $this->setAliasedXmlValue($rounding, $roundingNode, 'mode', 'm');
        $this->setAliasedXmlValue($rounding, $roundingNode, 'application', 'a');

        return $rounding;
    }

    private function extractComponents(\SimpleXMLElement $input): array
    {
        $componentsNode = $this->getFirstChild($input, 'components', 'cs');

        if ($componentsNode === null) {
            return [];
        }

        $components = [];

        foreach ($componentsNode->children() as $componentNode) {
            $component = [];
            $this->setAliasedXmlValue($component, $componentNode, 'net', 'n');
            $this->setAliasedXmlValue($component, $componentNode, 'gross', 'g');
            $this->setAliasedXmlValue($component, $componentNode, 'tax', 't');
            $this->setAliasedXmlValue($component, $componentNode, 'taxRate', 'tr');
            $this->setAliasedXmlValue($component, $componentNode, 'comment', 'c');
            $components[] = $component;
        }

        return $components;
    }

    private function getFirstChild(\SimpleXMLElement $input, string ...$names): ?\SimpleXMLElement
    {
        foreach ($names as $name) {
            if (isset($input->{$name}[0])) {
                return $input->{$name}[0];
            }
        }

        return null;
    }

    private function getChildValue(\SimpleXMLElement $input, string ...$names): ?string
    {
        $child = $this->getFirstChild($input, ...$names);

        if ($child === null) {
            return null;
        }

        return trim((string)$child);
    }

    private function getPrecisionValue(\SimpleXMLElement $input): ?int
    {
        $precision = $this->getChildValue($input, 'precision', 'pr');

        return $precision === null ? null : (int)$precision;
    }

    private function setAliasedValue(array &$target, string $fullName, string $compactName, mixed $value): void
    {
        $target[$fullName] = $value;
        $target[$compactName] = $value;
    }

    private function setAliasedXmlValue(
        array &$target,
        \SimpleXMLElement $input,
        string $fullName,
        string $compactName,
        mixed $value = null
    ): void {
        $this->setAliasedValue(
            $target,
            $fullName,
            $compactName,
            func_num_args() === 5 ? $value : $this->getAliasedChildValue($input, $fullName, $compactName)
        );
    }

    private function getAliasedChildValue(\SimpleXMLElement $input, string $fullName, string $compactName): ?string
    {
        return $this->getChildValue($input, $fullName, $compactName);
    }
}
