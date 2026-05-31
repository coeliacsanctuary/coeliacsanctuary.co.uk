<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\SyncShippingToGoogleMerchantAction;
use App\Enums\Shop\PostageArea;
use App\Enums\Shop\ShippingMethod;
use App\Models\Shop\ShopPostagePrice;
use App\Services\GoogleMerchant\GoogleMerchantShippingManager;
use Database\Seeders\ShopScaffoldingSeeder;
use Google\Service\ShoppingContent\ShippingSettings;
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

        $this->assertSame('letter-UK', $service->getName());
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
        $this->assertSame('UK', $service->getDeliveryCountry());
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
        $this->assertSame('0.0500', $weights[0]->getValue());
        $this->assertSame('0.1000', $weights[1]->getValue());
        $this->assertSame('infinity', $weights[2]->getValue());
        $this->assertSame('kg', $weights[0]->getUnit());
        $this->assertSame('1.50', $rows[0]->getCells()[0]->getFlatRate()->getValue());
        $this->assertSame('2.50', $rows[1]->getCells()[0]->getFlatRate()->getValue());
        $this->assertSame('4.00', $rows[2]->getCells()[0]->getFlatRate()->getValue());
        $this->assertSame('GBP', $rows[0]->getCells()[0]->getFlatRate()->getCurrency());
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
        $this->assertSame('5', $deliveryTime->getMinTransitTimeInDays());
        $this->assertSame('7', $deliveryTime->getMaxTransitTimeInDays());
        $this->assertSame('0', $deliveryTime->getMinHandlingTimeInDays());
        $this->assertSame('1', $deliveryTime->getMaxHandlingTimeInDays());
    }
}
