<?php
namespace App\Libraries;

final class Money
{
    public const MAX_CENTS = 9999999999;

    public static function cents(string $amount): int
    {
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $amount)) {
            throw new \InvalidArgumentException('Enter a price with at most two decimal places.');
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $cents = (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
        if ($cents < 1 || $cents > self::MAX_CENTS) {
            throw new \InvalidArgumentException('Price must be between 0.01 and 99,999,999.99.');
        }
        return $cents;
    }
    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
    public static function total(string $price, int $quantity): string
    {
        $cents = self::cents($price);
        if ($quantity < 1 || $quantity > 1000000 || $quantity > intdiv(self::MAX_CENTS, $cents)) {
            throw new \InvalidArgumentException('Quantity or sale total is outside the supported range.');
        }
        return self::decimal($cents * $quantity);
    }
}
