<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards\AppleWallet;

use App\Actions\Shop\MyTravelCards\GetOrCreateAppleWalletPassAction;
use App\Enums\Shop\DigitalCardType;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\StubMobilePass;
use Tests\TestCase;

class GetControllerTest extends TestCase
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
        $this->get(route('my-travel-cards.wallet.apple', $this->card))
            ->assertRedirect(route('my-travel-cards.login.show'));
    }

    #[Test]
    public function itBuildsThePassForTheCustomersCard(): void
    {
        $this->expectAction(GetOrCreateAppleWalletPassAction::class, [
            fn (ShopCustomer $customer, ShopDigitalCard $card) => $customer->is($this->customer) && $card->is($this->card),
        ], return: new StubMobilePass());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itServesThePassAsAPkpassDownload(): void
    {
        $this->expectAction(GetOrCreateAppleWalletPassAction::class, return: new StubMobilePass());

        $this->makeRequest()->assertHeader('content-type', 'application/vnd.apple.pkpass');
    }

    #[Test]
    public function itReturnsNotFoundWhenTheCustomerHasntBoughtTheCard(): void
    {
        $this->dontExpectAction(GetOrCreateAppleWalletPassAction::class);

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

        $this->dontExpectAction(GetOrCreateAppleWalletPassAction::class);

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

        $this->dontExpectAction(GetOrCreateAppleWalletPassAction::class);

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itBuildsThePassForACardThatIsNoLongerLive(): void
    {
        $this->card->update(['live' => false]);

        $this->expectAction(GetOrCreateAppleWalletPassAction::class, return: new StubMobilePass());

        $this->makeRequest()->assertOk();
    }

    protected function makeRequest(?ShopDigitalCard $card = null): TestResponse
    {
        return $this->actingAs($this->customer, 'customers')
            ->get(route('my-travel-cards.wallet.apple', $card ?? $this->card));
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
            'type_id' => DigitalCardType::STANDARD,
            'title' => 'French Travel Card',
            'slug' => 'french-travel-card',
            'language' => 'french',
            'native_language' => 'français',
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
