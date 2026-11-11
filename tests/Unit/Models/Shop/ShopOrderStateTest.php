<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderState;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ShopOrderStateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
    }

    #[Test]
    public function itHasOrders(): void
    {
        $this->build(ShopOrder::class)
            ->count(5)
            ->asCompleted()
            ->create();

        $state = ShopOrderState::query()->find(OrderState::SHIPPED);

        $this->assertInstanceOf(Collection::class, $state->orders);
    }
}
