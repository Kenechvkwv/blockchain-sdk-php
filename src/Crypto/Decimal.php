<?php

namespace BlockchainSdk\Crypto;

class Decimal
{
    /**
     * Convert any numeric value (string, float, int, scientific notation) into a clean fixed-point decimal string.
     */
    public static function toPlainString(string|float|int $amount, int $maxDecimals = 18): string
    {
        $amountStr = (string) $amount;
        if (! stripos($amountStr, 'e')) {
            return $amountStr;
        }

        $formatted = sprintf('%.*F', $maxDecimals, (float) $amount);

        return rtrim(rtrim($formatted, '0'), '.');
    }

    /**
     * Multiply an amount by 10^decimals into an integer base unit string without scientific notation.
     */
    public static function toBaseUnit(string|float|int $amount, int $decimals): string
    {
        $plain = self::toPlainString($amount, $decimals);

        return bcmul($plain, bcpow('10', (string) $decimals), 0);
    }

    /**
     * Convert an integer base unit string back to a formatted human-readable decimal string.
     */
    public static function fromBaseUnit(string|int $baseUnit, int $decimals, ?int $scale = null): string
    {
        $scale = $scale ?? $decimals;
        $divisor = bcpow('10', (string) $decimals);
        if ($divisor === '0') {
            return '0';
        }

        return bcdiv((string) $baseUnit, $divisor, $scale);
    }
}
