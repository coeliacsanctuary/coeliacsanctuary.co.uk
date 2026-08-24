<?php

declare(strict_types=1);

namespace App\Schema;

use Spatie\SchemaOrg\MerchantReturnEnumeration;
use Spatie\SchemaOrg\MerchantReturnPolicy;
use Spatie\SchemaOrg\ReturnMethodEnumeration;

class ShopReturnPolicySchema
{
    public static function make(): MerchantReturnPolicy
    {
        return (new MerchantReturnPolicy())
            ->applicableCountry('GB')
            ->merchantReturnDays(14)
            ->setProperty('returnPolicyCategory', MerchantReturnEnumeration::MerchantReturnFiniteReturnWindow)
            ->setProperty('returnMethod', ReturnMethodEnumeration::ReturnByMail);
    }
}
