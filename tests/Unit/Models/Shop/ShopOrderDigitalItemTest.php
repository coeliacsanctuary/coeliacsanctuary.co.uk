<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopOrderDigitalItemTest extends TestCase
{
    #[Test]
    public function itCanGetTheOrder(): void
    {
        $order = $this->create(ShopOrder::class);

        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->create();

        $this->assertInstanceOf(ShopOrder::class, $item->order);
    }

    #[Test]
    public function itBelongsToACard(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $item = $this->build(ShopOrderDigitalItem::class)
            ->forCard($card)
            ->create();

        $this->assertInstanceOf(ShopDigitalCard::class, $item->refresh()->card);
    }
}
