<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards\Login;

use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\MyTravelCards\OneTimeCodeNotification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\RequestFactories\MyTravelCardsLoginRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected ShopCustomer $customer;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        TestTime::freezeAtSecond();

        $this->customer = $this->create(ShopCustomer::class);

        $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create(['has_digital_items' => true]);
    }

    #[Test]
    public function itErrorsWithoutAnEmail(): void
    {
        $this->makeRequest(['email' => null])->assertSessionHasErrors('email');
    }

    #[Test]
    public function itErrorsWithAnInvalidEmail(): void
    {
        $this->makeRequest(['email' => 'foo'])->assertSessionHasErrors('email');
    }

    #[Test]
    public function itErrorsWithAnEmailThatDoesntBelongToACustomer(): void
    {
        $this->makeRequest(['email' => 'foo@bar.com'])->assertSessionHasErrors('email');
    }

    #[Test]
    public function itErrorsIfTheCustomerHasntPurchasedADigitalCard(): void
    {
        $customer = $this->create(ShopCustomer::class);

        $this->build(ShopOrder::class)
            ->asPaid($customer)
            ->create(['has_digital_items' => false]);

        $this->makeRequest(['email' => $customer->email])->assertSessionHasErrors('email');
    }

    #[Test]
    public function itAllowsACustomerThatHasPurchasedADigitalCard(): void
    {
        $this->makeRequest()->assertSessionDoesntHaveErrors('email');
    }

    #[Test]
    public function itStoresAHashedSixDigitOneTimeCodeAgainstTheCustomer(): void
    {
        $this->assertNull($this->customer->one_time_code);

        $this->makeRequest();

        $this->customer->refresh();

        $this->assertNotNull($this->customer->one_time_code);
        $this->assertTrue(Hash::check($this->oneTimeCodeFromNotification(), $this->customer->one_time_code));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $this->oneTimeCodeFromNotification());
    }

    #[Test]
    public function itExpiresTheOneTimeCodeInFifteenMinutes(): void
    {
        $this->makeRequest();

        $this->assertTrue($this->customer->refresh()->one_time_code_expires_at->equalTo(now()->addMinutes(15)));
    }

    #[Test]
    public function itNotifiesTheCustomerOfTheirOneTimeCode(): void
    {
        $this->makeRequest();

        Notification::assertSentTo($this->customer, OneTimeCodeNotification::class);
    }

    #[Test]
    public function itDoesntStoreAOneTimeCodeOrNotifyTheCustomerWhenTheRequestIsInvalid(): void
    {
        $this->makeRequest(['email' => 'foo']);

        $this->assertNull($this->customer->refresh()->one_time_code);

        Notification::assertNothingSent();
    }

    #[Test]
    public function itRedirectsToTheConfirmPage(): void
    {
        $this->makeRequest()->assertRedirect(route('my-travel-cards.login.confirm.show'));
    }

    #[Test]
    public function itStoresTheEmailInAnEncryptedCookie(): void
    {
        $response = $this->makeRequest();

        $cookie = $response->getCookie('my_travel_cards_email');

        $this->assertNotNull($cookie);
        $this->assertSame($this->customer->email, Crypt::decryptString($cookie->getValue()));
    }

    protected function oneTimeCodeFromNotification(): string
    {
        $code = null;

        Notification::assertSentTo(
            $this->customer,
            OneTimeCodeNotification::class,
            function (OneTimeCodeNotification $notification) use (&$code): bool {
                $code = $notification->toMail($this->customer)->data()['code'];

                return true;
            }
        );

        return $code;
    }

    protected function makeRequest(array $params = []): TestResponse
    {
        if ( ! array_key_exists('email', $params)) {
            $params['email'] = $this->customer->email;
        }

        return $this->post(
            route('my-travel-cards.login.store'),
            MyTravelCardsLoginRequestFactory::new()->create($params),
        );
    }
}
