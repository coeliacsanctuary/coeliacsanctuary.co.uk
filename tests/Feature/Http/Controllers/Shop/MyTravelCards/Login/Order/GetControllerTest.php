<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards\Login\Order;

use App\Actions\Shop\MyTravelCards\CheckIfCustomerCanAccessMyTravelCardsAction;
use App\Actions\Shop\MyTravelCards\LoginCustomerAction;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);

        $this->order = $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create(['has_digital_items' => true]);
    }

    #[Test]
    public function itRedirectsToTheLoginPageWhenTheLinkIsntSigned(): void
    {
        $this->dontExpectAction(CheckIfCustomerCanAccessMyTravelCardsAction::class);
        $this->dontExpectAction(LoginCustomerAction::class);

        $this->get(route('my-travel-cards.login.order.get', ['order' => $this->order->order_key]))
            ->assertRedirect(route('my-travel-cards.login.show'))
            ->assertSessionHasErrors(['email' => 'Your link has expired, please enter your email to access your travel cards']);
    }

    #[Test]
    public function itRedirectsToTheLoginPageWhenTheSignatureHasBeenTamperedWith(): void
    {
        $this->dontExpectAction(CheckIfCustomerCanAccessMyTravelCardsAction::class);
        $this->dontExpectAction(LoginCustomerAction::class);

        $this->get($this->signedLink() . '&foo=bar')
            ->assertRedirect(route('my-travel-cards.login.show'))
            ->assertSessionHasErrors(['email' => 'Your link has expired, please enter your email to access your travel cards']);
    }

    #[Test]
    public function itRedirectsToTheLoginPageWhenTheLinkHasExpired(): void
    {
        $this->dontExpectAction(CheckIfCustomerCanAccessMyTravelCardsAction::class);
        $this->dontExpectAction(LoginCustomerAction::class);

        $link = URL::temporarySignedRoute(
            'my-travel-cards.login.order.get',
            now()->subMinute(),
            ['order' => $this->order->order_key],
        );

        $this->get($link)
            ->assertRedirect(route('my-travel-cards.login.show'))
            ->assertSessionHasErrors(['email' => 'Your link has expired, please enter your email to access your travel cards']);
    }

    #[Test]
    public function itReturnsNotFoundForAnOrderThatDoesntExist(): void
    {
        $this->dontExpectAction(CheckIfCustomerCanAccessMyTravelCardsAction::class);
        $this->dontExpectAction(LoginCustomerAction::class);

        $this->get(URL::signedRoute('my-travel-cards.login.order.get', ['order' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itRedirectsAnAlreadyAuthenticatedCustomerAwayFromTheLink(): void
    {
        $this->dontExpectAction(CheckIfCustomerCanAccessMyTravelCardsAction::class);
        $this->dontExpectAction(LoginCustomerAction::class);

        $this->actingAs($this->customer, 'customers')
            ->get($this->signedLink())
            ->assertRedirect(route('my-travel-cards.index'));
    }

    #[Test]
    public function itChecksIfTheOrdersCustomerCanAccessMyTravelCards(): void
    {
        $this->expectAction(
            CheckIfCustomerCanAccessMyTravelCardsAction::class,
            [fn (ShopCustomer $customer) => $customer->is($this->customer)],
            return: true,
        );

        $this->makeRequest();
    }

    #[Test]
    public function itRedirectsToTheLoginPageWhenTheCustomerHasntPurchasedADigitalCard(): void
    {
        $this->order->update(['has_digital_items' => false]);

        $this->dontExpectAction(LoginCustomerAction::class);

        $this->makeRequest()
            ->assertRedirect(route('my-travel-cards.login.show'))
            ->assertSessionHasErrors(['email' => 'Sorry, you must have purchased a digital card to access My Travel Cards.']);
    }

    #[Test]
    public function itLogsInTheOrdersCustomer(): void
    {
        $this->expectAction(LoginCustomerAction::class, [fn (ShopCustomer $customer) => $customer->is($this->customer)]);

        $this->makeRequest();
    }

    #[Test]
    public function itRedirectsToMyTravelCards(): void
    {
        $this->makeRequest()->assertRedirect(route('my-travel-cards.index'));
    }

    protected function signedLink(): string
    {
        return URL::signedRoute('my-travel-cards.login.order.get', ['order' => $this->order->order_key]);
    }

    protected function makeRequest(): TestResponse
    {
        return $this->get($this->signedLink());
    }
}
