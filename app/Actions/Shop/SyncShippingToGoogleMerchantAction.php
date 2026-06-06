<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopPostagePrice;
use App\Models\Shop\ShopShippingMethod;
use App\Services\GoogleMerchant\GoogleMerchantShippingManager;
use App\Services\GoogleMerchant\Helpers;
use Google\Shopping\Merchant\Accounts\V1\DeliveryTime;
use Google\Shopping\Merchant\Accounts\V1\Headers;
use Google\Shopping\Merchant\Accounts\V1\RateGroup;
use Google\Shopping\Merchant\Accounts\V1\Row;
use Google\Shopping\Merchant\Accounts\V1\Service;
use Google\Shopping\Merchant\Accounts\V1\ShippingSettings;
use Google\Shopping\Merchant\Accounts\V1\Table;
use Google\Shopping\Merchant\Accounts\V1\Value;
use Illuminate\Support\Collection;

class SyncShippingToGoogleMerchantAction
{
    public function __construct(protected GoogleMerchantShippingManager $manager)
    {
    }

    public function handle(): void
    {
        if ( ! $this->manager->isEnabled()) {
            return;
        }

        $methods = ShopShippingMethod::query()
            ->with(['prices' => fn ($q) => $q->orderBy('max_weight')->with('area.countries')])
            ->get();

        $services = $methods->flatMap(
            fn (ShopShippingMethod $method) => $method->prices
                ->groupBy('postage_country_area_id')
                ->flatMap(function (Collection $prices) use ($method) {
                    $area = $prices->first()?->area;

                    if ($area === null) {
                        return [];
                    }

                    return $area->countries->map(
                        fn ($country) => $this->buildService($method, $country, $area->delivery_timescale, $prices)
                    );
                })
        )->all();

        $settings = new ShippingSettings();
        $settings->setName("accounts/{$this->manager->merchantId()}/shippingSettings");
        $settings->setServices($services);
        $settings->setEtag('');

        $this->manager->update($settings);
    }

    /** @param Collection<int, ShopPostagePrice> $prices */
    protected function buildService(ShopShippingMethod $method, mixed $country, string $deliveryTimescale, Collection $prices): Service
    {
        $isoCode = mb_strtoupper($country->iso_code);

        $service = new Service();
        $service->setServiceName("{$method->shipping_method}-{$isoCode}");
        $service->setActive(true);
        $service->setDeliveryCountries([$isoCode]);
        $service->setCurrencyCode('GBP');
        $service->setDeliveryTime($this->buildDeliveryTime($deliveryTimescale));

        $rateGroup = new RateGroup();
        $rateGroup->setApplicableShippingLabels([]);

        if ($prices->count() === 1) {
            $flatRate = new Value();
            $flatRate->setFlatRate(Helpers::priceFromPence($prices->first()->price));
            $rateGroup->setSingleValue($flatRate);
        } else {
            $rateGroup->setMainTable($this->buildWeightTable($prices));
        }

        $service->setRateGroups([$rateGroup]);

        return $service;
    }

    protected function buildDeliveryTime(string $timescale): DeliveryTime
    {
        preg_match('/(\d+)\s*-\s*(\d+)/', $timescale, $matches);

        $deliveryTime = new DeliveryTime();
        $deliveryTime->setMinHandlingDays(0);
        $deliveryTime->setMaxHandlingDays(1);
        $deliveryTime->setMinTransitDays((int) ($matches[1] ?? 0));
        $deliveryTime->setMaxTransitDays((int) ($matches[2] ?? 0));

        return $deliveryTime;
    }

    /** @param Collection<int, ShopPostagePrice> $prices */
    protected function buildWeightTable(Collection $prices): Table
    {
        $weights = [];
        $rows = [];
        $lastIndex = $prices->count() - 1;

        foreach ($prices->values() as $index => $price) {
            $weights[] = Helpers::weightFromGrams($index === $lastIndex ? null : $price->max_weight);

            $cell = new Value();
            $cell->setFlatRate(Helpers::priceFromPence($price->price));

            $row = new Row();
            $row->setCells([$cell]);
            $rows[] = $row;
        }

        $rowHeaders = new Headers();
        $rowHeaders->setWeights($weights);

        $table = new Table();
        $table->setRowHeaders($rowHeaders);
        $table->setRows($rows);

        return $table;
    }
}
