<?php

declare(strict_types=1);

namespace App\Services\GoogleMerchant;

use Google\Service\ShoppingContent;
use Google\Service\ShoppingContent\Product;

class GoogleMerchantProductManager
{
    protected ?ShoppingContent $shoppingContent = null;

    public function __construct(protected GoogleMerchantClient $client)
    {
    }

    public function insert(Product $product): Product
    {
        return $this->service()->products->insert($this->client->merchantId(), $product);
    }

    public function update(string $productId, Product $product): Product
    {
        return $this->service()->products->update($this->client->merchantId(), $productId, $product);
    }

    public function delete(string $productId): void
    {
        $this->service()->products->delete($this->client->merchantId(), $productId);
    }

    public function isEnabled(): bool
    {
        return $this->client->isEnabled();
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
