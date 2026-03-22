<?php

namespace FromDevelopersForDevelopers\RelMon;

interface MonetaryBasisInterface
{
    public function getNet(): null|string|int;
    public function getGross(): null|string|int;
    public function getTax(): null|string|int;
    public function getTaxRate(): null|string|int;

    public function getNetInMinors(): ?int;
    public function getGrossInMinors(): ?int;
    public function getTaxInMinors(): ?int;
    public function getTaxRateInMinors(): ?int;
}
