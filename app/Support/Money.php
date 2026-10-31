<?php

namespace App\Support;

/**
 * Money helpers. Amounts are stored as DECIMAL(12,2) and handled as floats
 * rounded to 2 places at every step, which is exact enough for invoicing.
 */
class Money
{
    public static function format(float|int|string|null $amount, ?string $currency = null): string
    {
        $amount = (float) $amount;
        $currency ??= config('zenray.billing.currency');
        $formatted = in_array($currency, self::LAKH_GROUPED, true)
            ? self::lakhGrouping(abs($amount))
            : number_format(abs($amount), 2);

        return ($amount < 0 ? '-' : '').self::symbol($currency).$formatted;
    }

    /**
     * Currencies written with lakh/crore digit grouping (1,15,00,000.00).
     */
    public const LAKH_GROUPED = ['BDT', 'INR', 'NPR', 'PKR', 'LKR'];

    /**
     * Group digits the South Asian way: last three, then pairs.
     */
    public static function lakhGrouping(float $amount): string
    {
        [$whole, $fraction] = explode('.', number_format($amount, 2, '.', ''));
        $lastThree = substr($whole, -3);
        $rest = substr($whole, 0, -3);

        if ($rest !== '') {
            $lastThree = ','.$lastThree;
            $rest = ltrim(strrev(implode(',', str_split(strrev($rest), 2))), ',');
        }

        return "{$rest}{$lastThree}.{$fraction}";
    }

    /**
     * Symbol for a currency: the configured one for the default currency,
     * otherwise the code followed by a space (e.g. "EUR 10.00").
     */
    public static function symbol(?string $currency = null): string
    {
        $currency ??= config('zenray.billing.currency');

        return $currency === config('zenray.billing.currency')
            ? (string) config('zenray.billing.currency_symbol', '')
            : "{$currency} ";
    }

    public static function round(float|int|string|null $amount): float
    {
        return round((float) $amount, 2);
    }
}
