<?php

namespace FromDevelopersForDevelopers\RelMon\Interface;

interface MonetaryBasisInterface
{
    public function getNetInMinors(): ?int;
    public function getGrossInMinors(): ?int;
    public function getTaxInMinors(): ?int;
    public function getTaxRateInMinors(): ?int;

    public function getPrecision(): int;
    public function getTaxRatePrecision(): int;
}
