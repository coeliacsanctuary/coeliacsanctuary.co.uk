<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

class Helpers
{
    public static function adminUser(): User
    {
        return User::query()->where('email', 'contact@coeliacsanctuary.co.uk')->firstOrFail();
    }

    public static function milesToMeters(float $miles): float
    {
        return round($miles * 1609.344);
    }

    public static function metersToMiles(float $meters): float
    {
        return $meters / 1609.344;
    }

    public static function formatMoney(Money $money): string
    {
        $numberFormatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);

        return (new IntlMoneyFormatter($numberFormatter, new ISOCurrencies()))->format($money);
    }
}
