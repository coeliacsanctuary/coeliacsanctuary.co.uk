<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopProduct;
use App\Services\GoogleMerchant\GoogleMerchantProductManager;
use App\Services\GoogleMerchant\Helpers;
use Google\Shopping\Merchant\Products\V1\Availability;
use Google\Shopping\Merchant\Products\V1\Condition;
use Google\Shopping\Merchant\Products\V1\ProductAttributes;
use Google\Shopping\Merchant\Products\V1\ProductInput;

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

        $product->update(['google_merchant_product_id' => $result->getName()]);
    }

    protected function removeFromMerchant(ShopProduct $product): void
    {
        if ( ! $product->google_merchant_product_id) {
            return;
        }

        $this->manager->delete($product->google_merchant_product_id);

        $product->update(['google_merchant_product_id' => null]);
    }

    protected function buildProduct(ShopProduct $product): ProductInput
    {
        $product->loadMissing(['variants', 'prices']);

        $attrs = new ProductAttributes();
        $attrs->setTitle($product->title);
        $attrs->setDescription($product->description);
        $attrs->setLink($product->absolute_link);
        $attrs->setImageLink($product->main_image);
        $attrs->setBrand('Coeliac Sanctuary');
        $attrs->setMpn($product->slug);
        $attrs->setCondition(Condition::PBNEW);
        $attrs->setAvailability(Availability::IN_STOCK);

        if ($product->oldPrice !== null) {
            $attrs->setPrice(Helpers::priceFromPence($product->oldPrice));
            $attrs->setSalePrice(Helpers::priceFromPence($product->currentPrice));
        } else {
            $attrs->setPrice(Helpers::priceFromPence($product->currentPrice));
        }

        $firstVariant = $product->variants->first();

        if ($firstVariant !== null) {
            $attrs->setShippingWeight(Helpers::shippingWeightFromGrams($firstVariant->weight));
        }

        return (new ProductInput())
            ->setOfferId((string) $product->id)
            ->setContentLanguage('en')
            ->setFeedLabel('GB')
            ->setProductAttributes($attrs);
    }
}
