<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Shop\SyncProductToGoogleMerchantJob;
use App\Jobs\Shop\SyncShippingToGoogleMerchantJob;
use App\Models\Shop\ShopProduct;
use App\Services\GoogleMerchant\GoogleMerchantProductManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

use function Laravel\Prompts\select;

class SyncToGoogleMerchantCommand extends Command
{
    protected $signature = 'coeliac:google-merchant:sync';

    protected $description = 'Bulk sync products and/or shipping settings to Google Merchant';

    public function handle(GoogleMerchantProductManager $productManager): void
    {
        if ( ! $productManager->isEnabled()) {
            $this->warn('Google Merchant is disabled. Enable GOOGLE_MERCHANT_ENABLED to run syncs.');

            return;
        }

        $selection = select(
            label: 'What would you like to sync?',
            options: [
                'all' => 'Products and shipping (recommended)',
                'products' => 'Products only',
                'shipping' => 'Shipping settings only',
            ],
        );

        if ($selection === 'all' || $selection === 'products') {
            ShopProduct::query()
                /** @phpstan-ignore-next-line */
                ->whereHas('variants', fn (Builder $q) => $q->where('live', true))
                ->lazy()
                ->each(function (ShopProduct $product): void {
                    SyncProductToGoogleMerchantJob::dispatch($product);
                    $this->info("Queued product sync for: {$product->title}");
                });
        }

        if ($selection === 'all' || $selection === 'shipping') {
            SyncShippingToGoogleMerchantJob::dispatch();
            $this->info('Queued shipping settings sync.');
        }
    }
}
