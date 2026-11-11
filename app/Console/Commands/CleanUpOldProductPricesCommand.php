<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Signature;
use App\Models\Shop\ShopPrice;
use Illuminate\Console\Command;

#[Signature('coeliac:clean-up-product-prices')]
class CleanUpOldProductPricesCommand extends Command
{
    public function handle(): void
    {
        ShopPrice::query()
            ->whereNotNull('end_at')
            ->where('end_at', '<', now())
            ->delete();
    }
}
