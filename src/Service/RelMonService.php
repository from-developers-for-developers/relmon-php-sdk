<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\Dto\CanonicalMonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\CanonicalRelMonDto;
use FromDevelopersForDevelopers\RelMon\Dto\MonetaryComponentDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
use FromDevelopersForDevelopers\RelMon\Enum\Format;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;
use FromDevelopersForDevelopers\RelMon\Exception\FormatNotSupportedException;
use FromDevelopersForDevelopers\RelMon\Exception\ProtocolIdentifierInvalidException;
use FromDevelopersForDevelopers\RelMon\Exception\ValidationException;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserFactory;
use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\ValueObject\MonetaryComponent;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;
use FromDevelopersForDevelopers\RelMon\ValueObject\RelMonObject;

class RelMonService
{
    public function __construct(
        private FormatParserFactory $formatParserFactory,
        private ValidationService   $validationService,
        private MinorsService       $minorsService,
        private DerivationService   $derivationService,
    )
    {
    }

    public function build(mixed $input, string $formatEnum = Format::AUTO): RelMonObject
    {
        try {
            $format = $this->guessFormat($input, $formatEnum);
            $formatParser = $this->formatParserFactory->createFormatParser($format);
            $dto = $formatParser->parse($input);
            $protocolIdentifier = new ProtocolIdentifier($dto->protocolIdentifier);

            $violations = $this->validationService->validate($protocolIdentifier, $dto);

            if (!empty($violations)) {
                throw new ValidationException($violations);
            }

            $canonicalDto = $this->buildCanonicalDto($protocolIdentifier, $dto);
            $components = [];

            foreach ($canonicalDto->getComponents() as $component) {
                list($net, $gross, $tax, $taxRate) = $this->getMinorNumbers(
                    $canonicalDto,
                    $component,
                    $canonicalDto->getScope() === Scope::COMPONENT
                );

                $components[] = new MonetaryComponent($net, $gross, $tax, $taxRate, $component->getComment());
            }

            list($net, $gross, $tax, $taxRate) = $this->getMinorNumbers(
                $canonicalDto,
                $canonicalDto,
                $canonicalDto->getScope() === Scope::ROOT
            );

            $relmon = new RelMonObject(
                net: $net,
                gross: $gross,
                tax: $tax,
                taxRate: $taxRate,
                unit: $canonicalDto->getUnit(),
                precision: $canonicalDto->getPrecision(),
                scope: $canonicalDto->getScope(),
                roundingMode: $canonicalDto->getRoundingMode(),
                roundingApplication: $canonicalDto->getRoundingApplication(),
                components: $components,
            );

            $this->compareAmounts($relmon);

            return $relmon;
        } catch (ProtocolIdentifierInvalidException $e) {
            throw new ValidationException([new ViolationDto($e->getMessage(), 'protocolIdentifier')]);
        }
    }

    private function guessFormat(mixed $input, string $formatEnum): string
    {
        if ($formatEnum !== Format::AUTO) {
            return $formatEnum;
        }

        if (is_array($input)) {
            return Format::JSON_ARRAY;
        } elseif (class_exists('\SimpleXMLElement') && $input instanceof \SimpleXMLElement) {
            return Format::XML_SIMPLE_XML;
        } elseif (class_exists('\DOMDocument') && $input instanceof \DOMDocument) {
            return Format::XML_DOM_DOCUMENT;
        } elseif (is_string($input)) {
            $input = ltrim($input);

            if (str_starts_with($input, 'relmon-json://')) {
                return Format::URI_JSON;
            }

            if (str_starts_with($input, 'relmon-xml://')) {
                return Format::URI_XML;
            }

            if (str_starts_with($input, 'relmon-min://')) {
                return Format::URI_MINIMALISTIC;
            }

            if (str_starts_with($input, '<')) {
                return Format::XML_STRING;
            }

            if (str_starts_with($input, '{')) {
                return Format::JSON_STRING;
            }
        }

        throw new FormatNotSupportedException();
    }

    private function buildCanonicalDto(ProtocolIdentifier $protocolIdentifier, RelMonDto $dto): CanonicalRelMonDto
    {
        $precision = $this->getPrecision($dto);
        $taxRatePrecision = $this->getTaxRatePrecision($dto);
        $components = [];

        foreach ($dto->components as $component) {
            $componentTaxRatePrecision = $this->getTaxRatePrecision($component, $taxRatePrecision);
            $components[] = new CanonicalMonetaryComponentDto(
                basis: $this->minorsService->toMinors($component, $precision, $componentTaxRatePrecision),
                comment: $component->getComment(),
            );
        }

        return new CanonicalRelMonDto(
            protocolIdentifier: $protocolIdentifier,
            scope: Scope::tryFrom((string)$dto->scope) ?? Scope::ROOT,
            roundingMode: RoundingMode::tryFrom((string)$dto->roundingMode) ?? RoundingMode::HALF_EVEN,
            roundingApplication: RoundingApplication::tryFrom($dto->roundingApplication) ?? RoundingApplication::TAX,
            basis: $this->minorsService->toMinors($dto, $precision, $taxRatePrecision),
            precision: $precision,
            taxRatePrecision: $taxRatePrecision,
            unit: $dto->unit,
            components: $components,
        );
    }

    private function compareAmounts(RelMonObject $relmon): void
    {
        if (empty($relmon->getComponents()) || $relmon->getScope() === Scope::ROOT) {
            return;
        }

        // Compare only if scope = c.
        $totalNet = 0;
        $totalGross = 0;
        $totalTax = 0;

        foreach ($relmon->getComponents() as $component) {
            $totalNet += $component->getNet();
            $totalGross += $component->getGross();
            $totalTax += $component->getTax();
        }

        $violations = [];

        if ($relmon->getNet() !== $totalNet) {
            $violations[] = new ViolationDto('Net amount does not match sum of component net amounts.', 'net');
        }

        if ($relmon->getGross() !== $totalGross) {
            $violations[] = new ViolationDto('Gross amount does not match sum of component gross amounts.', 'gross');
        }

        if ($relmon->getTax() !== $totalTax) {
            $violations[] = new ViolationDto('Tax amount does not match sum of component tax amounts.', 'tax');
        }

        if (empty($violations)) {
            return;
        }

        throw new ValidationException($violations);
    }

    private function getPrecision(RelMonDto $dto): int
    {
        if (is_int($dto->precision)) {
            return $dto->precision;
        }

        foreach ([$dto->net, $dto->gross, $dto->tax] as $basis) {
            if (is_null($basis)) {
                continue;
            }

            if (is_int($basis)) {
                // Cannot determine precision from minors.
                return 0;
            }

            $basis = explode('.', $basis);

            if (count($basis) === 1) {
                continue;
            }

            return strlen($basis[1]);
        }

        return 0;
    }

    private function getTaxRatePrecision(RelMonDto|MonetaryComponentDto $dto, int $default = 0): int
    {
        if (is_null($dto->getTaxRate())) {
            return $default;
        }

        $taxRate = (string)$dto->getTaxRate();

        if (!str_contains($taxRate, '.')) {
            return $default;
        }

        $taxRate = explode('.', $taxRate);

        return strlen($taxRate[1]);
    }

    private function getMinorNumbers(
        CanonicalRelMonDto     $CanonicalRelMonDto,
        MonetaryBasisInterface $basis,
        bool                   $derive
    ): array
    {
        $net = $basis->getNetInMinors();
        $gross = $basis->getGrossInMinors();
        $tax = $basis->getTaxInMinors();
        $taxRate = $basis->getTaxRateInMinors();

        if ($derive) {
            $derived = $this->derivationService->derive($CanonicalRelMonDto, $basis);
            $net = $derived->getNetInMinors();
            $gross = $derived->getGrossInMinors();
            $tax = $derived->getTaxInMinors();
            $taxRate = $derived->getTaxRateInMinors();
        }

        return [$net, $gross, $tax, $taxRate];
    }
}
