<?php

namespace FromDevelopersForDevelopers\RelMon\Service;

use Decimal\Decimal;
use FromDevelopersForDevelopers\RelMon\Service\MinorsService;
use FromDevelopersForDevelopers\RelMon\Dto\DerivedResultDto;
use FromDevelopersForDevelopers\RelMon\Dto\RelMonDto;
use FromDevelopersForDevelopers\RelMon\Enum\FormatEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplicationEnum;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingModeEnum;
use FromDevelopersForDevelopers\RelMon\Enum\ScopeEnum;
use FromDevelopersForDevelopers\RelMon\Exception\FormatNotSupportedException;
use FromDevelopersForDevelopers\RelMon\Exception\ValidationException;
use FromDevelopersForDevelopers\RelMon\FormatParser\FormatParserFactory;
use FromDevelopersForDevelopers\RelMon\MonetaryBasisInterface;
use FromDevelopersForDevelopers\RelMon\MonetaryComponent;
use FromDevelopersForDevelopers\RelMon\ProtocolIdentifier;
use FromDevelopersForDevelopers\RelMon\RelMonObject;

class RelMonService
{
    public function __construct(
        private FormatParserFactory $formatParserFactory,
        private MinorsService       $minorsService,
        private DerivationService   $derivationService,
    )
    {
    }

    public function build(mixed $input, FormatEnum $formatEnum = FormatEnum::AUTO): RelMonObject
    {
        $format = $this->guessFormat($input, $formatEnum);
        $formatParser = $this->formatParserFactory->createFormatParser($format);
        $dto = $formatParser->parse($input);
        $violations = $this->validate($dto);

        if (!empty($violations)) {
            throw new ValidationException($violations);
        }

        $protocolIdentifier = new ProtocolIdentifier($dto->protocolIdentifier);
        $precision = $this->getPrecision($dto);
        $roundingMode = $this->getRoundingMode($dto);
        $roundingApplication = $this->getRoundingApplication($dto);
        $taxRatePrecision = $this->getTaxRatePrecision($dto);

        $minors = $this->minorsService->toMinors($dto, $precision, $taxRatePrecision);
        $dto->setMinors($minors);

        $components = [];

        if ($dto->scope === ScopeEnum::COMPONENT->value) {
            foreach ($dto->components as $component) {
                $taxRatePrecision ??= $this->getTaxRatePrecision($component);
                $minors = $this->minorsService->toMinors($component, $precision, $taxRatePrecision);
                $component->setMinors($minors);

                $componentDerivation = $this->derivationService->derive(
                    $component,
                    $protocolIdentifier,
                    $roundingMode,
                    $roundingApplication
                );

                $components[] = new MonetaryComponent(
                    net: $dto->protocolIdentifier->isInMinorsMode()
                        ? $componentDerivation->net
                        : new Decimal($componentDerivation->net, $precision),

                    gross: $dto->protocolIdentifier->isInMinorsMode()
                        ? $component->gross
                        : new Decimal($component->gross, $precision),

                    tax: $dto->protocolIdentifier->isInMinorsMode()
                        ? $component->tax
                        : new Decimal(0, $precision),

                    taxRate: $component->taxRate,
                    comment: $component->comment,
                );
            }
        }

        $rootDerivation = $this->derivationService->derive($dto);

        $this->compareAmounts($rootDerivation, $components);

        return new RelMonObject(
            net: $dto->protocolIdentifier->isInMinorsMode()
                ? $rootDerivation->net
                : new Decimal($rootDerivation->net, $precision),

            gross: $dto->protocolIdentifier->isInMinorsMode()
                ? $rootDerivation->gross
                : new Decimal($rootDerivation->gross, $precision),

            tax: $dto->protocolIdentifier->isInMinorsMode()
                ? $rootDerivation->tax
                : new Decimal($rootDerivation->tax, $precision),

            taxRate: $dto->taxRate,
            unit: $dto->unit,
            precision: $precision,
            scope: $dto->scope ?? ScopeEnum::ROOT,
            roundingMode: $dto->roundingMode ?? RoundingModeEnum::HALF_EVEN,
            roundingApplication: $dto->roundingApplication ?? RoundingApplicationEnum::TAX,
            components: $components,
        );
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

            if (json_validate($input)) {
                return FormatEnum::JSON_STRING;
            }
        }

        throw new FormatNotSupportedException();
    }

    private function validate(RelMonDto $dto): array
    {
        // @TODO: implement validation
        return [];
    }

    private function compareAmounts(DerivedResultDto $rootDerivedResult, array $components): void
    {
        // @TODO: add comparison and throw an exception
    }

    private function getPrecision(RelMonDto $dto): int
    {
        if (is_int($dto->precision)) {
            return $dto->precision;
        }

        // @TODO: get precision from net/gross/tax values
        return 2;
    }

    private function getRoundingMode(RelMonDto $dto): RoundingModeEnum
    {
        return $dto->roundingMode ? RoundingModeEnum::from($dto->roundingMode) : RoundingModeEnum::HALF_EVEN;
    }

    private function getRoundingApplication(RelMonDto $dto): RoundingApplicationEnum
    {
        return $dto->roundingApplication
            ? RoundingApplicationEnum::from($dto->roundingApplication)
            : RoundingApplicationEnum::TAX;
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
