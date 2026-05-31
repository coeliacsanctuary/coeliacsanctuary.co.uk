<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopPostagePrice;
use App\Models\Shop\ShopShippingMethod;
use App\Services\GoogleMerchant\GoogleMerchantShippingManager;
use Google\Service\ShoppingContent\DeliveryTime;
use Google\Service\ShoppingContent\Headers;
use Google\Service\ShoppingContent\Price;
use Google\Service\ShoppingContent\RateGroup;
use Google\Service\ShoppingContent\Row;
use Google\Service\ShoppingContent\Service;
use Google\Service\ShoppingContent\ShippingSettings;
use Google\Service\ShoppingContent\Table;
use Google\Service\ShoppingContent\Value;
use Google\Service\ShoppingContent\Weight;
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
        $settings->setAccountId($this->manager->merchantId());
        $settings->setServices($services);

        $this->manager->update($settings);
    }

    /** @param Collection<int, ShopPostagePrice> $prices */
    protected function buildService(ShopShippingMethod $method, mixed $country, string $deliveryTimescale, Collection $prices): Service
    {
        $isoCode = mb_strtoupper($country->iso_code);

        $service = new Service();
        $service->setName("{$method->shipping_method}-{$isoCode}");
        $service->setActive(true);
        $service->setDeliveryCountry($isoCode);
        $service->setCurrency('GBP');
        $service->setDeliveryTime($this->buildDeliveryTime($deliveryTimescale));

        $rateGroup = new RateGroup();
        $rateGroup->setApplicableShippingLabels([]);
        $rateGroup->setMainTable($this->buildWeightTable($prices));
        $service->setRateGroups([$rateGroup]);

        return $service;
    }

    protected function buildDeliveryTime(string $timescale): DeliveryTime
    {
        preg_match('/(\d+)\s*-\s*(\d+)/', $timescale, $matches);

        $deliveryTime = new DeliveryTime();
        $deliveryTime->setMinHandlingTimeInDays('0');
        $deliveryTime->setMaxHandlingTimeInDays('1');
        $deliveryTime->setMinTransitTimeInDays($matches[1] ?? '0');
        $deliveryTime->setMaxTransitTimeInDays($matches[2] ?? '0');

        return $deliveryTime;
    }

    /** @param Collection<int, ShopPostagePrice> $prices */
    protected function buildWeightTable(Collection $prices): Table
    {
        $weights = [];
        $rows = [];
        $lastIndex = $prices->count() - 1;

        foreach ($prices->values() as $index => $price) {
            $weightValue = $index === $lastIndex
                ? 'infinity'
                : number_format($price->max_weight / 1000, 4, '.', '');

            $weights[] = new Weight(['value' => $weightValue, 'unit' => 'kg']);

            $cell = new Value();
            $cell->setFlatRate(new Price([
                'value' => number_format($price->price / 100, 2, '.', ''),
                'currency' => 'GBP',
            ]));

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
