<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Basket\DigitalItem;

use App\Actions\Shop\ResolveBasketAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class DestroyControllerTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopOrderDigitalItem $digitalItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = ShopOrder::query()->create();

        $this->digitalItem = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($this->order)
            ->create();
    }

    #[Test]
    public function itCallsTheResolveBasketAction(): void
    {
        $this->expectAction(ResolveBasketAction::class, [$this->order->token, false]);

        $this->makeRequest();
    }

    #[Test]
    public function itReturnsNotFoundIfThereIsNoBasket(): void
    {
        $this->delete(route('shop.basket.digital.remove', ['digitalItem' => $this->digitalItem->id]))
            ->assertNotFound();
    }

    #[Test]
    public function itDoesntRemoveTheDigitalItemIfThereIsNoBasket(): void
    {
        $this->delete(route('shop.basket.digital.remove', ['digitalItem' => $this->digitalItem->id]));

        $this->assertModelExists($this->digitalItem);
    }

    #[Test]
    public function itReturnsNotFoundIfTheDigitalItemIsntInTheUsersBasket(): void
    {
        $digitalItem = $this->create(ShopOrderDigitalItem::class);

        $this->makeRequest($digitalItem->id)->assertNotFound();
    }

    #[Test]
    public function itDoesntRemoveADigitalItemFromAnotherBasket(): void
    {
        $digitalItem = $this->create(ShopOrderDigitalItem::class);

        $this->makeRequest($digitalItem->id);

        $this->assertModelExists($digitalItem);
    }

    #[Test]
    public function itRemovesTheShopOrderDigitalItemRow(): void
    {
        $this->makeRequest();

        $this->assertModelMissing($this->digitalItem);
    }

    #[Test]
    public function itTouchesTheOrderUpdateTimestamp(): void
    {
        TestTime::addMinutes(30);

        $this->makeRequest();

        $this->assertTrue($this->order->refresh()->updated_at->isSameSecond(now()));
    }

    #[Test]
    public function itRedirectsBack(): void
    {
        $this
            ->from(route('shop.basket.checkout'))
            ->makeRequest()
            ->assertRedirectToRoute('shop.basket.checkout');
    }

    protected function makeRequest(?int $digitalItem = null): TestResponse
    {
        return $this->withCookie('basket_token', $this->order->token)
            ->delete(route('shop.basket.digital.remove', ['digitalItem' => $digitalItem ?? $this->digitalItem->id]));
    }
}
