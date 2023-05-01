<?php
declare(strict_types = 1);

namespace App;

use Brick\Math\BigNumber;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Money;
use InvalidArgumentException;

final class Amount
{
    private const ROUNDING_MODE = RoundingMode::HALF_UP;
    private int $amount;
    private string $currency;

    private function __construct(int $amount, string $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @param BigNumber|float|int|string $amount
     * @param string                     $currency
     *
     * @return Amount
     *
     * @throws InvalidArgumentException
     */
    public static function of($amount, string $currency): self
    {
        $amountAsMoney = self::parseAndValidateOrFail($amount, $currency);

        return new self(
            $amountAsMoney->getMinorAmount()->toInt(),
            $amountAsMoney->getCurrency()->getCurrencyCode(),
        );
    }

    public function equalsTo(self $secondAmount): bool
    {
        try {
            return $this->amount()->isEqualTo($secondAmount->amount());
        } catch (MoneyMismatchException $e) {
            return false;
        }
    }

    public function amount(): Money
    {
        return Money::ofMinor($this->amount, $this->currency, null, self::ROUNDING_MODE);
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function parseAndValidateOrFail($amount, string $currency): Money
    {
        try {
            return Money::of($amount, strtoupper($currency), null, self::ROUNDING_MODE);
        } catch (UnknownCurrencyException $e) {
            throw new InvalidArgumentException("Invalid currency: $currency");
        } catch (NumberFormatException $e) {
            throw new InvalidArgumentException("Invalid amount format: $amount");
        }
    }
}