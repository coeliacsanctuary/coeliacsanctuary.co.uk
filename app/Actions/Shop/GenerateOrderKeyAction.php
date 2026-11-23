<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopOrder;
use Illuminate\Support\Str;

class GenerateOrderKeyAction
{
    public function handle(): string
    {
        $key = $this->generateKey();

        if (ShopOrder::query()->where('order_key', $key)->exists()) {
            return $this->handle();
        }

        return $key;
    }

    protected function generateKey(): string
    {
        return Str::password(8, letters: false, symbols: false);
    }
}
