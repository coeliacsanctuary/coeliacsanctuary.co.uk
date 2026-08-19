<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Shop\ShopCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public static function formatSearchTerm(string $term): string
    {
        if (Str::of($term)->trim()->isMatch('/^[a-z]{1,2}\d/i')) {
            return Str::upper(mb_trim($term));
        }

        return Str::apa($term);
    }

    /** @return int<1, max> */
    public static function readingTime(string $body): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($body)) / 200));
    }

    public static function formatMoney(Money $money): string
    {
        $numberFormatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);

        return (new IntlMoneyFormatter($numberFormatter, new ISOCurrencies()))->format($money);
    }

    public static function requestIsFromApp(Request $request): bool
    {
        return (bool) ($request->userAgent() && Str::of($request->userAgent())->contains(['CoeliacSanctuaryOntheGo', 'okhttp'], true));
    }

    public static function isTravelCard(int|ShopCategory|null $category): bool
    {
        if ($category === null) {
            return false;
        }

        return in_array($category instanceof ShopCategory ? $category->id : $category, [1, 11], true);
    }
}
