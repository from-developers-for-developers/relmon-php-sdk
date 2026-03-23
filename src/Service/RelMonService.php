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
use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\MonetaryComponent;
use FromDevelopersForDevelopers\RelMon\ProtocolIdentifier;
use FromDevelopersForDevelopers\RelMon\RelMonObject;
use FromDevelopersForDevelopers\RelMon\ValueObject\DerivedResult;
use FromDevelopersForDevelopers\RelMon\ValueObject\ValidatedMonetaryComponent;
use FromDevelopersForDevelopers\RelMon\ValueObject\ValidatedRelMon;

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

            if ($dto->getScope() === ScopeEnum::COMPONENT) {
                foreach ($dto->getComponents() as $component) {
                    $derived = $this->derivationService->derive($dto, $component);
                    $components[] = new MonetaryComponent(
                        net: $derived->getNetInMinors(),
                        gross: $derived->getGrossInMinors(),
                        tax: $derived->getTaxInMinors(),
                        taxRate: $derived->getTaxRateInMinors(),
                        comment: $component->getComment(),
                    );
                }
            }

            $derived = $this->derivationService->derive($dto, $dto);
            $this->compareAmounts($derived, $components);

            return new RelMonObject(
                net: $derived->getNetInMinors(),
                gross: $derived->getGrossInMinors(),
                tax: $derived->getTaxInMinors(),
                taxRate: $derived->getTaxRateInMinors(),
                unit: $dto->getUnit(),
                precision: $dto->getPrecision(),
                scope: $dto->getScope(),
                roundingMode: $dto->getRoundingMode(),
                roundingApplication: $dto->getRoundingApplication(),
                components: $components,
            );
        } catch (ProtocolIdentifierInvalidException $e) {
            throw new ValidationException([new ViolationDto($e->getMessage(), 'protocolIdentifier')]);
        } catch (\Throwable $e) {
            // @TODO
        }
    }

    private function guessFormat(mixed $input, FormatEnum $formatEnum): FormatEnum
    {
        if ($formatEnum !== FormatEnum::AUTO) {
            return $formatEnum;
        }

        if (is_array($input)) {
            return FormatEnum::JSON_ARRAY;
        } elseif ($input instanceof \SimpleXMLElement) {
            return FormatEnum::XML_SIMPLE_XML;
        } elseif ($input instanceof \DOMDocument) {
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
            $components[] = new ValidatedMonetaryComponent(
                minorsBasis: $this->minorsService->toMinors($component, $precision),
                taxRatePrecision: $taxRatePrecision ?? $this->getTaxRatePrecision($component),
                comment: $component->getComment(),
            );
        }

        return new ValidatedRelMon(
            protocolIdentifier: $protocolIdentifier,
            scope: ScopeEnum::tryFrom((string)$dto->scope) ?? ScopeEnum::ROOT,
            roundingMode: RoundingModeEnum::tryFrom((string)$dto->roundingMode) ?? RoundingModeEnum::HALF_EVEN,
            roundingApplication: RoundingApplicationEnum::tryFrom($dto->roundingApplication) ?? RoundingApplicationEnum::TAX,
            minorsBasis: $this->minorsService->toMinors($dto, $precision),
            unit: $dto->unit,
            precision: $precision,
            taxRatePrecision: $taxRatePrecision,
            components: $components,
        );
    }

    private function compareAmounts(DerivedResult $rootDerivedResult, array $components): void
    {
        if (empty($components)) {
            return;
        }

        $totalNet = 0;
        $totalGross = 0;
        $totalTax = 0;

        // @TODO: components might not have all net/gross/tax fields, fix this (for instance if scope = root)
        foreach ($components as $component) {
            $totalNet += $component->getNet();
            $totalGross += $component->getGross();
            $totalTax += $component->getTax();
        }

        $violations = [];

        if ($rootDerivedResult->getNetInMinors() !== $totalNet) {
            $violations[] = new ViolationDto('net', 'Net amount does not match sum of component net amounts.');
        }

        if ($rootDerivedResult->getGrossInMinors() !== $totalGross) {
            $violations[] = new ViolationDto('gross', 'Gross amount does not match sum of component gross amounts.');
        }

        if ($rootDerivedResult->getTaxInMinors() !== $totalTax) {
            $violations[] = new ViolationDto('tax', 'Tax amount does not match sum of component tax amounts.');
        }

        if (empty($violations)) {
            return;
        }

        throw new ValidationException($violations);
    }

    private function getPrecision(RelMonDto $dto): ?int
    {
        if (is_int($dto->precision)) {
            return $dto->precision;
        }

        // @TODO: get properly max precision
        foreach ([$dto->net, $dto->gross, $dto->tax] as $basis) {
            if (is_int($basis)) {
                // Can not determine precision from minors.
                return null;
            }

            $basis = explode('.', $basis);

            if (count($basis) === 1) {
                continue;
            }

            return strlen($basis[1]);
        }

        return null;
    }

    private function getTaxRatePrecision(MonetaryBasisInterface $basis): ?int
    {
        if (is_null($basis->getTaxRate())) {
            return null;
        }

        $taxRate = (string)$basis->getTaxRate();

        if (!str_contains($taxRate, '.')) {
            return 0;
        }

        $taxRate = explode('.', $taxRate);

        return strlen($taxRate[1]);
    }
}
