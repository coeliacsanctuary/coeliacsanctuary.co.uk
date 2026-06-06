<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\SyncShippingToGoogleMerchantAction;
use App\Enums\Shop\PostageArea;
use App\Enums\Shop\ShippingMethod;
use App\Models\Shop\ShopPostagePrice;
use App\Services\GoogleMerchant\GoogleMerchantShippingManager;
use Database\Seeders\ShopScaffoldingSeeder;
use Google\Shopping\Merchant\Accounts\V1\ShippingSettings;
use Google\Shopping\Type\Weight\WeightUnit;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncShippingToGoogleMerchantActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
    }

    #[Test]
    public function itDoesNothingWhenGlobalMerchantIsDisabled(): void
    {
        $this->mock(GoogleMerchantShippingManager::class)
            ->shouldReceive('isEnabled')
            ->andReturn(false)
            ->shouldNotReceive('update');

        $this->callAction(SyncShippingToGoogleMerchantAction::class);
    }

    #[Test]
    public function itCallsUpdateOnTheManager(): void
    {
        $this->build(ShopPostagePrice::class)->create([
            'shipping_method_id' => ShippingMethod::LETTER->value,
            'postage_country_area_id' => PostageArea::UK->value,
            'max_weight' => 100,
            'price' => 200,
        ]);

        $this->mock(GoogleMerchantShippingManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('merchantId')->andReturn('12345')
            ->shouldReceive('update')
            ->with(Mockery::type(ShippingSettings::class))
            ->once();

        $this->callAction(SyncShippingToGoogleMerchantAction::class);
    }

    #[Test]
    public function itBuildsOneServicePerShippingMethodAndCountryCombination(): void
    {
        $this->build(ShopPostagePrice::class)->create([
            'shipping_method_id' => ShippingMethod::LETTER->value,
            'postage_country_area_id' => PostageArea::EUROPE->value,
            'max_weight' => 100,
            'price' => 350,
        ]);

        $capturedSettings = null;

        $this->mock(GoogleMerchantShippingManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('merchantId')->andReturn('12345')
            ->shouldReceive('update')
            ->withArgs(function (ShippingSettings $settings) use (&$capturedSettings) {
                $capturedSettings = $settings;

                return true;
            });

        $this->callAction(SyncShippingToGoogleMerchantAction::class);

        // Europe area has 1 country in ShopScaffoldingSeeder (France)
        $this->assertCount(1, $capturedSettings->getServices());
    }

    #[Test]
    public function itSetsTheServiceNameFromMethodAndCountry(): void
    {
        $this->build(ShopPostagePrice::class)->create([
            'shipping_method_id' => ShippingMethod::LETTER->value,
            'postage_country_area_id' => PostageArea::UK->value,
            'max_weight' => 100,
            'price' => 200,
        ]);

        $capturedSettings = null;

        $this->mock(GoogleMerchantShippingManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('merchantId')->andReturn('12345')
            ->shouldReceive('update')
            ->withArgs(function (ShippingSettings $settings) use (&$capturedSettings) {
                $capturedSettings = $settings;

                return true;
            });

        $this->callAction(SyncShippingToGoogleMerchantAction::class);

        $service = $capturedSettings->getServices()[0];

        $this->assertSame('letter-UK', $service->getServiceName());
    }

    #[Test]
    public function itUppercasesTheIsoCode(): void
    {
        $this->build(ShopPostagePrice::class)->create([
            'shipping_method_id' => ShippingMethod::LETTER->value,
            'postage_country_area_id' => PostageArea::UK->value,
            'max_weight' => 100,
            'price' => 200,
        ]);

        $capturedSettings = null;

        $this->mock(GoogleMerchantShippingManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('merchantId')->andReturn('12345')
            ->shouldReceive('update')
            ->withArgs(function (ShippingSettings $settings) use (&$capturedSettings) {
                $capturedSettings = $settings;

                return true;
            });

        $this->callAction(SyncShippingToGoogleMerchantAction::class);

        $service = $capturedSettings->getServices()[0];

        // ShopScaffoldingSeeder creates UK with iso_code 'uk' (lowercase)
        $this->assertSame('UK', $service->getDeliveryCountries()[0]);
    }

    #[Test]
    public function itBuildsWeightBasedPricingRows(): void
    {
        foreach ([50 => 150, 100 => 250, 200 => 400] as $weight => $price) {
            $this->build(ShopPostagePrice::class)->create([
                'shipping_method_id' => ShippingMethod::LETTER->value,
                'postage_country_area_id' => PostageArea::UK->value,
                'max_weight' => $weight,
                'price' => $price,
            ]);
        }

        $capturedSettings = null;

        $this->mock(GoogleMerchantShippingManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('merchantId')->andReturn('12345')
            ->shouldReceive('update')
            ->withArgs(function (ShippingSettings $settings) use (&$capturedSettings) {
                $capturedSettings = $settings;

                return true;
            });

        $this->callAction(SyncShippingToGoogleMerchantAction::class);

        $service = $capturedSettings->getServices()[0];
        $table = $service->getRateGroups()[0]->getMainTable();
        $weights = $table->getRowHeaders()->getWeights();
        $rows = $table->getRows();

        $this->assertCount(3, $rows);
        // 50g = 50_000 micros, 100g = 100_000 micros, last row = -1 (infinity)
        $this->assertSame(50_000, $weights[0]->getAmountMicros());
        $this->assertSame(100_000, $weights[1]->getAmountMicros());
        $this->assertSame(-1, $weights[2]->getAmountMicros());
        $this->assertSame(WeightUnit::KILOGRAM, $weights[0]->getUnit());
        // 150p = 1_500_000 micros, 250p = 2_500_000 micros, 400p = 4_000_000 micros
        $this->assertSame(1_500_000, $rows[0]->getCells()[0]->getFlatRate()->getAmountMicros());
        $this->assertSame(2_500_000, $rows[1]->getCells()[0]->getFlatRate()->getAmountMicros());
        $this->assertSame(4_000_000, $rows[2]->getCells()[0]->getFlatRate()->getAmountMicros());
        $this->assertSame('GBP', $rows[0]->getCells()[0]->getFlatRate()->getCurrencyCode());
    }

    #[Test]
    public function itSetsDeliveryTimescaleOnDeliveryTime(): void
    {
        $this->build(ShopPostagePrice::class)->create([
            'shipping_method_id' => ShippingMethod::LETTER->value,
            'postage_country_area_id' => PostageArea::EUROPE->value,
            'max_weight' => 100,
            'price' => 350,
        ]);

        $capturedSettings = null;

        $this->mock(GoogleMerchantShippingManager::class)
            ->shouldReceive('isEnabled')->andReturn(true)
            ->shouldReceive('merchantId')->andReturn('12345')
            ->shouldReceive('update')
            ->withArgs(function (ShippingSettings $settings) use (&$capturedSettings) {
                $capturedSettings = $settings;

                return true;
            });

        $this->callAction(SyncShippingToGoogleMerchantAction::class);

        $deliveryTime = $capturedSettings->getServices()[0]->getDeliveryTime();

        // PostageArea::EUROPE deliveryEstimate = "5 - 7"
        $this->assertSame(5, $deliveryTime->getMinTransitDays());
        $this->assertSame(7, $deliveryTime->getMaxTransitDays());
        $this->assertSame(0, $deliveryTime->getMinHandlingDays());
        $this->assertSame(1, $deliveryTime->getMaxHandlingDays());
    }
}
