<?php

declare(strict_types=1);

namespace App\Services\GoogleMerchant;

use Google\Service\ShoppingContent;
use Google\Service\ShoppingContent\ShippingSettings;

class GoogleMerchantShippingManager
{
    protected ?ShoppingContent $shoppingContent = null;

    public function __construct(protected GoogleMerchantClient $client)
    {
    }

    public function update(ShippingSettings $settings): ShippingSettings
    {
        $merchantId = $this->client->merchantId();

        return $this->service()->shippingsettings->update($merchantId, $merchantId, $settings);
    }

    public function isEnabled(): bool
    {
        return $this->client->isEnabled();
    }

    public function merchantId(): string
    {
        return $this->client->merchantId();
    }

    public function setShoppingContent(ShoppingContent $shoppingContent): void
    {
        $this->shoppingContent = $shoppingContent;
    }

    protected function service(): ShoppingContent
    {
        return $this->shoppingContent ??= new ShoppingContent($this->client->client());
    }
}
