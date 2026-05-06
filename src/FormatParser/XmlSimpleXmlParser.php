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
        $this->setAliasedValue($data, 'protocol', 'p', $this->getChildValue($input, 'protocol', 'p'));
        $this->setAliasedValue($data, 'net', 'n', $this->getChildValue($input, 'net', 'n'));
        $this->setAliasedValue($data, 'gross', 'g', $this->getChildValue($input, 'gross', 'g'));
        $this->setAliasedValue($data, 'tax', 't', $this->getChildValue($input, 'tax', 't'));
        $this->setAliasedValue($data, 'taxRate', 'tr', $this->getChildValue($input, 'taxRate', 'tr'));
        $this->setAliasedValue($data, 'unit', 'u', $this->getChildValue($input, 'unit', 'u'));
        $this->setAliasedValue($data, 'precision', 'pr', $this->getPrecisionValue($input));
        $this->setAliasedValue($data, 'scope', 's', $this->getChildValue($input, 'scope', 's'));

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
        $mode = $this->getChildValue($roundingNode, 'mode', 'm');
        $application = $this->getChildValue($roundingNode, 'application', 'a');

        $this->setAliasedValue($rounding, 'mode', 'm', $mode);
        $this->setAliasedValue($rounding, 'application', 'a', $application);

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
            $this->setAliasedValue($component, 'net', 'n', $this->getChildValue($componentNode, 'net', 'n'));
            $this->setAliasedValue($component, 'gross', 'g', $this->getChildValue($componentNode, 'gross', 'g'));
            $this->setAliasedValue($component, 'tax', 't', $this->getChildValue($componentNode, 'tax', 't'));
            $this->setAliasedValue($component, 'taxRate', 'tr', $this->getChildValue($componentNode, 'taxRate', 'tr'));
            $this->setAliasedValue($component, 'comment', 'c', $this->getChildValue($componentNode, 'comment', 'c'));
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
}
