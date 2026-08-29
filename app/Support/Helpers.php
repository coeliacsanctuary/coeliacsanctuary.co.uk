<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Shop\ShopCategory;
use App\Models\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use League\ISO3166\ISO3166;
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

    public static function isTravelCard(int|ShopCategory|null $category): bool
    {
        if ($category === null) {
            return false;
        }

        return in_array($category instanceof ShopCategory ? $category->id : $category, [1, 11], true);
    }

    public static function countryCode(string $country): ?string
    {
        try {
            return match (Str::lower($country)) {
                'england' => 'gb-eng',
                'wales' => 'gb-wls',
                'scotland', 'orkney islands', 'shetland islands' => 'gb-sct',
                'america', 'usa' => 'us',
                'channel islands' => 'gb',
                'czech republic' => 'cz',
                'turkey' => 'tr',
                'vietnam' => 'vn',
                'south korea' => 'kr',
                'north korea' => 'kp',
                'laos' => 'la',
                'burma' => 'mm',
                'democratic republic of the congo' => 'cd',
                'vatican city' => 'va',
                'curacao' => 'cw',
                'aland islands' => 'ax',
                'st lucia' => 'lc',
                default => Str::lower(Arr::get(app(ISO3166::class)->name($country), 'alpha2')),
            };
        } catch (Exception) {
            return null;
        }
    }
}
