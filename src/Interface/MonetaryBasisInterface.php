<?php

namespace FromDevelopersForDevelopers\RelMon\Interface;

interface MonetaryBasisInterface
{
    public function getNet(): null|string|int;
    public function getGross(): null|string|int;
    public function getTax(): null|string|int;
    public function getTaxRate(): null|string|int;
}
