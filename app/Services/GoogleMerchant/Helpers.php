<?php

declare(strict_types=1);

namespace App\Services\GoogleMerchant;

use Google\Shopping\Merchant\Products\V1\ShippingWeight;
use Google\Shopping\Type\Price;
use Google\Shopping\Type\Weight;
use Google\Shopping\Type\Weight\WeightUnit;

class Helpers
{
    public static function priceFromPence(int $pence): Price
    {
        return (new Price())
            ->setAmountMicros($pence * 10_000)
            ->setCurrencyCode('GBP');
    }

    public static function shippingWeightFromGrams(int $grams): ShippingWeight
    {
        return (new ShippingWeight())
            ->setValue($grams / 1000)
            ->setUnit('kg');
    }

    /** Pass null to represent an unbounded (infinity) weight tier. */
    public static function weightFromGrams(?int $grams): Weight
    {
        return (new Weight())
            ->setUnit(WeightUnit::KILOGRAM)
            ->setAmountMicros($grams === null ? -1 : $grams * 1_000);
    }
}
