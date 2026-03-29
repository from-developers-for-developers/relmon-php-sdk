<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
use FromDevelopersForDevelopers\RelMon\Enum\FormatEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Enum\ScopeEnum;
use FromDevelopersForDevelopers\RelMon\Exception\FormatNotSupportedException;
use FromDevelopersForDevelopers\RelMon\Exception\ProtocolIdentifierInvalidException;
use FromDevelopersForDevelopers\RelMon\Exception\ValidationException;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserFactory;
use FromDevelopersForDevelopers\RelMon\Interface\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\ValueObject\MonetaryComponent;
use FromDevelopersForDevelopers\RelMon\ValueObject\ProtocolIdentifier;
use FromDevelopersForDevelopers\RelMon\ValueObject\RelMonObject;
use FromDevelopersForDevelopers\RelMon\ValueObject\ValidatedMonetaryComponent;
use FromDevelopersForDevelopers\RelMon\ValueObject\ValidatedRelMon;

class RelMonService
{
    public function __construct(
        private readonly FormatParserFactory $formatParserFactory,
        private readonly ValidationService   $validationService,
        private readonly MinorsService       $minorsService,
        private readonly DerivationService   $derivationService,
    )
    {
    }

    public function build(mixed $input, FormatEnum $formatEnum = FormatEnum::AUTO): RelMonObject
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

            $dto = $this->buildValidatedDto($protocolIdentifier, $dto);

            $components = [];

            foreach ($dto->getComponents() as $component) {
                $net = $component->getNetInMinors();
                $gross = $component->getGrossInMinors();
                $tax = $component->getTaxInMinors();
                $taxRate = $component->getTaxRateInMinors();

                if ($dto->getScope() === ScopeEnum::COMPONENT) {
                    $derived = $this->derivationService->derive($dto, $component);
                    $net = $derived->getNetInMinors();
                    $gross = $derived->getGrossInMinors();
                    $tax = $derived->getTaxInMinors();
                    $taxRate = $derived->getTaxRateInMinors();
                }

                $components[] = new MonetaryComponent($net, $gross, $tax, $taxRate, $component->getComment());
            }

            $net = $dto->getNetInMinors();
            $gross = $dto->getGrossInMinors();
            $tax = $dto->getTaxInMinors();
            $taxRate = $dto->getTaxRateInMinors();

            if ($dto->getScope() === ScopeEnum::ROOT) {
                $derived = $this->derivationService->derive($dto, $dto);
                $net = $derived->getNetInMinors();
                $gross = $derived->getGrossInMinors();
                $tax = $derived->getTaxInMinors();
                $taxRate = $derived->getTaxRateInMinors();
            }

            $relmon = new RelMonObject(
                net: $net,
                gross: $gross,
                tax: $tax,
                taxRate: $taxRate,
                unit: $dto->getUnit(),
                precision: $dto->getPrecision(),
                scope: $dto->getScope(),
                roundingMode: $dto->getRoundingMode(),
                roundingApplication: $dto->getRoundingApplication(),
                components: $components,
            );

            $this->compareAmounts($relmon);

            return $relmon;
        } catch (ProtocolIdentifierInvalidException $e) {
            throw new ValidationException([new ViolationDto($e->getMessage(), 'protocolIdentifier')]);
        }
    }

    private function guessFormat(mixed $input, FormatEnum $formatEnum): FormatEnum
    {
        if ($formatEnum !== FormatEnum::AUTO) {
            return $formatEnum;
        }

        if (is_array($input)) {
            return FormatEnum::JSON_ARRAY;
        } elseif (class_exists('\SimpleXMLElement') && $input instanceof \SimpleXMLElement) {
            return FormatEnum::XML_SIMPLE_XML;
        } elseif (class_exists('\DOMDocument') && $input instanceof \DOMDocument) {
            return FormatEnum::XML_DOM_DOCUMENT;
        } elseif (is_string($input)) {
            $input = ltrim($input);

            if (str_starts_with($input, 'relmon-json://')) {
                return FormatEnum::URI_JSON;
            }

            if (str_starts_with($input, 'relmon-xml://')) {
                return FormatEnum::URI_XML;
            }

            if (str_starts_with($input, 'relmon-min://')) {
                return FormatEnum::URI_MINIMALISTIC;
            }

            if (str_starts_with($input, '<')) {
                return FormatEnum::XML_STRING;
            }

            if (str_starts_with($input, '{')) {
                return FormatEnum::JSON_STRING;
            }
        }

        throw new FormatNotSupportedException();
    }

    private function buildValidatedDto(ProtocolIdentifier $protocolIdentifier, RelMonDto $dto): ValidatedRelMon
    {
        $precision = $this->getPrecision($dto);
        $taxRatePrecision = $this->getTaxRatePrecision($dto);
        $components = [];

        foreach ($dto->components as $component) {
            $componentTaxRatePrecision = $this->getTaxRatePrecision($component, $taxRatePrecision);
            $components[] = new ValidatedMonetaryComponent(
                minorsBasis: $this->minorsService->toMinors($component, $precision, $componentTaxRatePrecision),
                comment: $component->getComment(),
            );
        }

        return new ValidatedRelMon(
            protocolIdentifier: $protocolIdentifier,
            scope: ScopeEnum::tryFrom((string)$dto->scope) ?? ScopeEnum::ROOT,
            roundingMode: RoundingModeEnum::tryFrom((string)$dto->roundingMode) ?? RoundingModeEnum::HALF_EVEN,
            roundingApplication: RoundingApplicationEnum::tryFrom($dto->roundingApplication) ?? RoundingApplicationEnum::TAX,
            minorsBasis: $this->minorsService->toMinors($dto, $precision, $taxRatePrecision),
            unit: $dto->unit,
            precision: $precision,
            taxRatePrecision: $taxRatePrecision,
            components: $components,
        );
    }

    private function compareAmounts(RelMonObject $relmon): void
    {
        if (empty($relmon->getComponents())) {
            return;
        }

        $totalNet = 0;
        $totalGross = 0;
        $totalTax = 0;

        // @TODO: components might not have all net/gross/tax fields, fix this (for instance if scope = root)
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

        // @TODO: get properly max precision
        foreach ([$dto->net, $dto->gross, $dto->tax] as $basis) {
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

    private function getTaxRatePrecision(MonetaryBasisInterface $basis, int $default = 0): int
    {
        if (is_null($basis->getTaxRate())) {
            return $default;
        }

        $taxRate = (string)$basis->getTaxRate();

        if (!str_contains($taxRate, '.')) {
            return $default;
        }

        $taxRate = explode('.', $taxRate);

        return strlen($taxRate[1]);
    }
}
