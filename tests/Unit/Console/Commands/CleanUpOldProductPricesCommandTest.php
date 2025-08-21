<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Models\Shop\ShopPrice;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class CleanUpOldProductPricesCommandTest extends TestCase
{
    #[Test]
    public function itDoesntDeleteProductPricesWithoutAnEndAt(): void
    {
        $price = $this->create(ShopPrice::class);

        $this->assertNull($price->end_at);

        $this->artisan('coeliac:clean-up-product-prices');

        $this->assertModelExists($price);
    }

    #[Test]
    public function itDoesntDeleteProductPricesWithAnEndAtInTheFuture(): void
    {
        $price = $this->create(ShopPrice::class, [
            'end_at' => now()->addDay(),
        ]);

        $this->assertNotNull($price->end_at);

        $this->artisan('coeliac:clean-up-product-prices');

        $this->assertModelExists($price);
    }

    #[Test]
    public function itDeletesProductPricesThatHaveEnded(): void
    {
        TestTime::freeze();

        $price = $this->create(ShopPrice::class, [
            'end_at' => now(),
        ]);

        TestTime::addMinute();

        $this->artisan('coeliac:clean-up-product-prices');

        $this->assertModelMissing($price);
    }
}
