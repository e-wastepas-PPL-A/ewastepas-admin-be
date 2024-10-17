<?php

namespace App\Helpers;


class CurrencyHelper
{
    public static function currencyIDR($value)
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    public static function totalPPN($price)
    {
        $persentasePPN = 11; //> 11%
        $ppn = $price * ($persentasePPN / 100);
        return $price + $ppn;
    }

    public static function getPercent($price, $percent, $decimals = 2)
    {
        if ($price == 0) {
            return 0;
        }

        return ($percent / 100) * $price;
    }
}
