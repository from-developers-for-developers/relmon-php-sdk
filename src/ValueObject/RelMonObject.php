<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

use FromDevelopersForDevelopers\RelMon\Enum\RoundingApplication;
use FromDevelopersForDevelopers\RelMon\Enum\RoundingMode;
use FromDevelopersForDevelopers\RelMon\Enum\Scope;

class RelMonObject
{
    public function __construct(
        private int $net,
        private int $gross,
        private int $tax,
        private ?int $taxRate = null,
        private ?string $unit = null,
        private ?int $precision = null,
        private ?int $taxRatePrecision = null,
        private string $scope = Scope::ROOT,
        private string $roundingMode = RoundingMode::HALF_EVEN,
        private string $roundingApplication = RoundingApplication::TAX,
        /** @var MonetaryComponent[] */
        private array $components = [],
    ) {
    }

    public function getNet(): int
    {
        return $this->net;
    }

    public function getGross(): int
    {
        return $this->gross;
    }

    public function getTax(): int
    {
        return $this->tax;
    }

    public function getTaxRate(): ?int
    {
        return $this->taxRate;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function getTaxRatePrecision(): ?int
    {
        return $this->taxRatePrecision;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getRoundingMode(): string
    {
        return $this->roundingMode;
    }

    public function getRoundingApplication(): string
    {
        return $this->roundingApplication;
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    public function getNetFormatted(string $decimalSeparator = '.', string $thousandsSeparator = ''): string
    {
        return $this->formatValue($this->net, $this->precision ?? 0, $decimalSeparator, $thousandsSeparator);
    }

    public function getGrossFormatted(string $decimalSeparator = '.', string $thousandsSeparator = ''): string
    {
        return $this->formatValue($this->gross, $this->precision ?? 0, $decimalSeparator, $thousandsSeparator);
    }

    public function getTaxFormatted(string $decimalSeparator = '.', string $thousandsSeparator = ''): string
    {
        return $this->formatValue($this->tax, $this->precision ?? 0, $decimalSeparator, $thousandsSeparator);
    }

    public function getTaxRateFormatted(string $decimalSeparator = '.', string $thousandsSeparator = ''): ?string
    {
        return $this->formatValue($this->taxRate, $this->taxRatePrecision ?? 0, $decimalSeparator, $thousandsSeparator);
    }

    private function formatValue(
        ?int $value,
        int $precision,
        string $decimalSeparator,
        string $thousandsSeparator
    ): ?string {
        if (is_null($value)) {
            return null;
        }

        $sign = $value < 0 ? '-' : '';
        $digits = (string)abs($value);

        if ($precision === 0) {
            return $sign . $this->addThousandsSeparator($digits, $thousandsSeparator);
        }

        $digits = str_pad($digits, $precision + 1, '0', STR_PAD_LEFT);
        $whole = substr($digits, 0, -$precision);
        $fraction = substr($digits, -$precision);

        return sprintf(
            '%s%s%s%s',
            $sign,
            $this->addThousandsSeparator($whole, $thousandsSeparator),
            $decimalSeparator,
            $fraction
        );
    }

    private function addThousandsSeparator(string $digits, string $thousandsSeparator): string
    {
        if ($thousandsSeparator === '') {
            return $digits;
        }

        return preg_replace('/\B(?=(\d{3})+(?!\d))/', $thousandsSeparator, $digits) ?? $digits;
    }
}
