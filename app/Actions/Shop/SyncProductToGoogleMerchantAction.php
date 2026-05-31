<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopProduct;
use App\Services\GoogleMerchant\GoogleMerchantProductManager;
use Google\Service\ShoppingContent\Price;
use Google\Service\ShoppingContent\Product;

class SyncProductToGoogleMerchantAction
{
    public function __construct(protected GoogleMerchantProductManager $manager)
    {
    }

    public function handle(ShopProduct $product): void
    {
        if ( ! $this->manager->isEnabled()) {
            return;
        }

        if ( ! $product->google_merchant_enabled) {
            $this->removeFromMerchant($product);

            return;
        }

        if ( ! $product->isInStock()) {
            $this->removeFromMerchant($product);

            return;
        }

        $googleProduct = $this->buildProduct($product);

        $result = $product->google_merchant_product_id
            ? $this->manager->update($product->google_merchant_product_id, $googleProduct)
            : $this->manager->insert($googleProduct);

        $product->update(['google_merchant_product_id' => $result->getId()]);
    }

    protected function removeFromMerchant(ShopProduct $product): void
    {
        if ( ! $product->google_merchant_product_id) {
            return;
        }

        $this->manager->delete($product->google_merchant_product_id);

        $product->update(['google_merchant_product_id' => null]);
    }

    protected function buildProduct(ShopProduct $product): Product
    {
        $googleProduct = new Product();

        $googleProduct->setOfferId((string) $product->id);
        $googleProduct->setTitle($product->title);
        $googleProduct->setLink($product->absolute_link);
        $googleProduct->setImageLink($product->main_image);
        $googleProduct->setPrice(new Price([
            'value' => number_format($product->currentPrice / 100, 2, '.', ''),
            'currency' => 'GBP',
        ]));
        $googleProduct->setAvailability('in_stock');
        $googleProduct->setChannel('online');
        $googleProduct->setContentLanguage('en');
        $googleProduct->setTargetCountry('GB');

        return $googleProduct;
    }

}
