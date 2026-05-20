<?php

namespace FromDevelopersForDevelopers\RelMon\ValueObject;

class MonetaryComponent
{
    public function __construct(
        private ?int $net,
        private ?int $gross,
        private ?int $tax,
        private ?int $taxRate = null,
        private ?string $comment = null,
        private ?int $precision = null,
        private ?int $taxRatePrecision = null,
    ) {
    }

    public function getNet(): ?int
    {
        return $this->net;
    }

    public function getGross(): ?int
    {
        return $this->gross;
    }

    public function getTax(): ?int
    {
        return $this->tax;
    }

    public function getTaxRate(): ?int
    {
        return $this->taxRate;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getNetFormatted(string $decimalSeparator = '.', string $thousandsSeparator = ''): ?string
    {
        return $this->formatValue($this->net, $this->precision ?? 0, $decimalSeparator, $thousandsSeparator);
    }

    public function getGrossFormatted(string $decimalSeparator = '.', string $thousandsSeparator = ''): ?string
    {
        return $this->formatValue($this->gross, $this->precision ?? 0, $decimalSeparator, $thousandsSeparator);
    }

    public function getTaxFormatted(string $decimalSeparator = '.', string $thousandsSeparator = ''): ?string
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
