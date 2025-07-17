<?php

declare(strict_types=1);

namespace App\Nova;

use Jpeters8889\AddressField\FieldServiceProvider as AddressFieldServiceProvider;
use Jpeters8889\AdvancedNovaMediaLibrary\AdvancedNovaMediaLibraryServiceProvider;
use Jpeters8889\Body\FieldServiceProvider as BodyFieldServiceProvider;
use Jpeters8889\CountryIcon\FieldServiceProvider as CountryFieldServiceProvider;
use Jpeters8889\EateryOpeningTimes\FieldServiceProvider as EateryOpeningTimesFieldServiceProvider;
use Jpeters8889\HiddenWritableField\FieldServiceProvider as HiddenWritableFieldServiceProvider;
use Jpeters8889\PolymorphicPanel\FieldServiceProvider as PolymorphicPanelFieldServiceProvider;
use Jpeters8889\ShopGenerateResendSlipButton\FieldServiceProvider as ShopGenerateResendSlipButtonFieldServiceProvider;
use Jpeters8889\ShopOrderOpenDispatchSlip\FieldServiceProvider as ShopDispatchSlipFieldServiceProvider;
use Jpeters8889\ShopOrderShippingAction\FieldServiceProvider as ShopShippingActionFieldServiceProvider;

/**
 * @codeCoverageIgnore
 */
class FieldRegistrar
{
    public static function handle(): void
    {
        $customFields = [
            AddressFieldServiceProvider::class,
            AdvancedNovaMediaLibraryServiceProvider::class,
            BodyFieldServiceProvider::class,
            CountryFieldServiceProvider::class,
            EateryOpeningTimesFieldServiceProvider::class,
            PolymorphicPanelFieldServiceProvider::class,
            ShopDispatchSlipFieldServiceProvider::class,
            ShopShippingActionFieldServiceProvider::class,
            ShopGenerateResendSlipButtonFieldServiceProvider::class,
            HiddenWritableFieldServiceProvider::class,
        ];

        foreach ($customFields as $customField) {
            app()->register($customField);
        }
    }
}
