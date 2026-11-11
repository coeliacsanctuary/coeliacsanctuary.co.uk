<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use Tests\TestCase;

class ShopOrderReviewItemTest extends TestCase
{
    #[Test]
    public function itBelongsToAProduct(): void
    {
        $product = $this->create(ShopProduct::class);

        $reviewItem = $this->build(ShopOrderReviewItem::class)
            ->forProduct($product)
            ->create();

        $this->assertInstanceOf(ShopProduct::class, $reviewItem->product()->withoutGlobalScopes()->first());
    }

    #[Test]
    public function itCanBelongToADigitalCard(): void
    {
        $digitalCard = $this->create(ShopDigitalCard::class);

        $reviewItem = $this->build(ShopOrderReviewItem::class)
            ->forDigitalCard($digitalCard)
            ->create();

        $this->assertInstanceOf(ShopDigitalCard::class, $reviewItem->product);
        $this->assertTrue($reviewItem->product->is($digitalCard));
    }

    #[Test]
    public function itCanGetItsParentReview(): void
    {
        $review = $this->create(ShopOrderReview::class);

        $item = $this->build(ShopOrderReviewItem::class)
            ->forReview($review)
            ->create();

        $this->assertInstanceOf(ShopOrderReview::class, $item->parent);
    }

    #[Test]
    public function itCanGetItsOrder(): void
    {
        $order = $this->create(ShopOrder::class);

        $item = $this->build(ShopOrderReviewItem::class)
            ->forOrder($order)
            ->create();

        $this->assertInstanceOf(ShopOrder::class, $item->order);
    }
}
