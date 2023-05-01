<?php

namespace App;

class Country
{
    public string $name;
    public string $currency;
    public string $countryCode;

    public function __construct(string $name, string|null $currency, string|null $countryCode)
    {
        $this->name = $name;
        $this->currency = $currency;
        $this->countryCode = $countryCode;
    }
}