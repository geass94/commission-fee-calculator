<?php
declare(strict_types=1);

namespace App\DTO;

use Brick\Math\BigDecimal;

class Transaction
{
    private string $bin;
    private Amount $amount;
    private string $currency;

    const EU_COUNTRY_CODES = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK',];

    public function __construct(string $bin, string|int|float $amount, string $currency)
    {
        $currency = strtoupper($currency);
        $this->bin = $bin;
        $this->amount = Amount::of($amount, $currency);
        $this->currency = $currency;
    }

    public function getAmount(): BigDecimal
    {
        return $this->amount->amount()->getAmount();
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getBin(): string
    {
        return $this->bin;
    }

    /**
     * @throws \Exception
     * @return float Commission fee rate based on country
     */
    public function getCommissionMultiplier(bool $isFromEU): float
    {
        return $isFromEU ? 0.01 : 0.02;
    }

}