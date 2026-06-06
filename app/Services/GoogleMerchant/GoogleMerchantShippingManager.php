<?php

declare(strict_types=1);

namespace App\Services\GoogleMerchant;

use Google\Shopping\Merchant\Accounts\V1\Client\ShippingSettingsServiceClient;
use Google\Shopping\Merchant\Accounts\V1\InsertShippingSettingsRequest;
use Google\Shopping\Merchant\Accounts\V1\ShippingSettings;

class GoogleMerchantShippingManager
{
    protected ?ShippingSettingsServiceClient $serviceClient = null;

    public function __construct(protected GoogleMerchantClient $client)
    {
    }

    public function update(ShippingSettings $settings): ShippingSettings
    {
        $request = (new InsertShippingSettingsRequest())
            ->setParent("accounts/{$this->client->merchantId()}")
            ->setShippingSetting($settings);

        return $this->service()->insertShippingSettings($request);
    }

    public function isEnabled(): bool
    {
        return $this->client->isEnabled();
    }

    public function merchantId(): string
    {
        return $this->client->merchantId();
    }

    protected function service(): ShippingSettingsServiceClient
    {
        return $this->serviceClient ??= new ShippingSettingsServiceClient([
            'credentials' => $this->client->client(),
            'transport' => 'rest',
        ]);
    }
}
