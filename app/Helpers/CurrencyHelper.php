<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format a number as currency using the configured currency settings
     */
    public static function format($amount)
    {
        $currency = config('app.currency');
        
        $formattedAmount = number_format(
            $amount,
            $currency['decimals'],
            $currency['decimal_separator'],
            $currency['thousands_separator']
        );
        
        return $currency['symbol'] . $formattedAmount;
    }
    
    /**
     * Get the currency symbol
     */
    public static function symbol()
    {
        return config('app.currency.symbol');
    }
    
    /**
     * Get the currency code
     */
    public static function code()
    {
        return config('app.currency.code');
    }
} 