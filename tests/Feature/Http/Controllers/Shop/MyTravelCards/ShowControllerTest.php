<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards;

use App\Enums\Shop\DigitalCardType;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopOrder $order;

    protected ShopDigitalCard $card;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);
        $this->order = $this->orderFor($this->customer);
        $this->card = $this->card();

        $this->purchase($this->card);
    }

    #[Test]
    public function itRedirectsGuestsToTheLoginPage(): void
    {
        $this->get(route('my-travel-cards.show', $this->card))->assertRedirect(route('my-travel-cards.login.show'));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itRendersTheMyTravelCardsShowPage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->component('Shop/MyTravelCards/Show'));
    }

    #[Test]
    public function itSetsThePageTitle(): void
    {
        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page->where('meta.title', "{$this->card->title} - My Travel Cards")->etc()
        );
    }

    #[Test]
    public function itDoesntTrackThePage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->where('meta.doNotTrack', true)->etc());
    }

    #[Test]
    public function itDoesntRenderAnyMetaTagsOrDescription(): void
    {
        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->where('meta.tags', [])
                ->where('meta.description', '')
                ->etc()
        );
    }

    #[Test]
    public function itReturnsTheCardDetails(): void
    {
        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->where('card.id', $this->card->id)
                ->where('card.type', 'standard')
                ->where('card.title', 'French Travel Card')
                ->where('card.language', 'french')
                ->where('card.native_language', 'français')
                ->etc()
        );
    }

    #[Test]
    public function itReturnsTheCardContent(): void
    {
        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->where('card.content.title', 'I am coeliac')
                ->where('card.content.intro', 'I cannot eat gluten')
                ->where('card.content.cant_eat.label', 'I cant eat')
                ->where('card.content.cant_eat.items', 'Wheat, barley, rye')
                ->where('card.content.cant_eat.colour.hex', '#ff0000')
                ->where('card.content.cant_eat.icon', 'cross')
                ->where('card.content.can_eat.label', 'I can eat')
                ->where('card.content.can_eat.items', 'Rice, potatoes')
                ->where('card.content.can_eat.colour.hex', '#00e800')
                ->where('card.content.can_eat.icon', 'tick')
                ->where('card.content.outro', 'Please check with the chef')
                ->where('card.content.thank_you', 'Thank you')
                ->etc()
        );
    }

    #[Test]
    public function itReturnsTheHashedOrderKeyAsTheId(): void
    {
        $this->order->update(['order_key' => '12345678']);

        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->where('id', md5('12345678'))->etc());
    }

    #[Test]
    public function itReturnsACardThatIsNoLongerLive(): void
    {
        $this->card->update(['live' => false]);

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itReturnsNotFoundWhenTheCustomerHasntBoughtTheCard(): void
    {
        $this->makeRequest($this->card(['title' => 'Spanish Travel Card', 'slug' => 'spanish-travel-card']))
            ->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundWhenAnotherCustomerBoughtTheCard(): void
    {
        /** @var ShopCustomer $otherCustomer */
        $otherCustomer = $this->create(ShopCustomer::class);

        $card = $this->card(['title' => 'Spanish Travel Card', 'slug' => 'spanish-travel-card']);

        $this->purchase($card, $this->orderFor($otherCustomer));

        $this->makeRequest($card)->assertNotFound();
    }

    public static function unfulfilledOrderStateDataProvider(): array
    {
        return [
            'basket' => [OrderState::BASKET],
            'pending' => [OrderState::PENDING],
            'refunded' => [OrderState::REFUNDED],
            'cancelled' => [OrderState::CANCELLED],
            'expired' => [OrderState::EXPIRED],
        ];
    }

    #[Test]
    #[DataProvider('unfulfilledOrderStateDataProvider')]
    public function itReturnsNotFoundWhenTheOrderIsntPaid(OrderState $state): void
    {
        $this->order->update(['state_id' => $state]);

        $this->makeRequest()->assertNotFound();
    }

    protected function makeRequest(?ShopDigitalCard $card = null): TestResponse
    {
        return $this->actingAs($this->customer, 'customers')->get(route('my-travel-cards.show', $card ?? $this->card));
    }

    protected function orderFor(ShopCustomer $customer, OrderState $state = OrderState::PAID): ShopOrder
    {
        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->forCustomer($customer)->create([
            'state_id' => $state,
            'order_key' => '12345678',
        ]);

        return $order;
    }

    protected function card(array $attributes = []): ShopDigitalCard
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)->create([
            'type' => DigitalCardType::STANDARD,
            'title' => 'French Travel Card',
            'slug' => 'french-travel-card',
            'language' => 'french',
            'native_language' => 'français',
            'card_title' => 'I am coeliac',
            'card_intro' => 'I cannot eat gluten',
            'cant_eat_items' => 'Wheat, barley, rye',
            'can_eat_items' => 'Rice, potatoes',
            'card_outro' => 'Please check with the chef',
            'card_thank_you' => 'Thank you',
            ...$attributes,
        ]);

        return $card;
    }

    protected function purchase(ShopDigitalCard $card, ?ShopOrder $order = null): void
    {
        $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order ?? $this->order)
            ->forCard($card)
            ->create();
    }
}
