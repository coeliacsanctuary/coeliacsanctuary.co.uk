<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards\Login\Confirm;

use App\Models\Shop\ShopCustomer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\RequestFactories\MyTravelCardsConfirmOneTimeCodeRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected ShopCustomer $customer;

    public function setUp(): void
    {
        parent::setUp();

        TestTime::freezeAtSecond();

        $this->customer = $this->create(ShopCustomer::class, [
            'one_time_code' => '123456',
            'one_time_code_expires_at' => now()->addMinutes(15),
        ]);
    }

    #[Test]
    public function itRedirectsToTheLoginPageWithoutTheEmailCookie(): void
    {
        $this->post(route('my-travel-cards.login.confirm.store'), MyTravelCardsConfirmOneTimeCodeRequestFactory::new()->create())
            ->assertRedirect(route('my-travel-cards.login.show'));

        $this->assertGuest('customers');
    }

    #[Test]
    public function itErrorsWithoutACode(): void
    {
        $this->makeRequest(['code' => null])->assertSessionHasErrors('code');
    }

    #[Test]
    public function itErrorsWithANonNumericCode(): void
    {
        $this->makeRequest(['code' => 'abcdef'])->assertSessionHasErrors('code');
    }

    #[Test]
    public function itErrorsWithACodeThatIsntSixDigits(): void
    {
        $this->makeRequest(['code' => '1234'])->assertSessionHasErrors('code');
    }

    #[Test]
    public function itErrorsWhenTheEmailInTheCookieDoesntBelongToACustomer(): void
    {
        $this->makeRequest(email: 'foo@bar.com')->assertNotFound();
    }

    #[Test]
    public function itErrorsWithAnIncorrectCode(): void
    {
        $this->makeRequest(['code' => '654321'])
            ->assertSessionHasErrors(['code' => 'The one time code you entered is incorrect, please try again.']);

        $this->assertGuest('customers');
    }

    #[Test]
    public function itDoesntClearTheOneTimeCodeWithAnIncorrectCode(): void
    {
        $this->makeRequest(['code' => '654321']);

        $this->customer->refresh();

        $this->assertNotNull($this->customer->one_time_code);
        $this->assertNotNull($this->customer->one_time_code_expires_at);
    }

    #[Test]
    public function itRedirectsToTheLoginPageWhenTheOneTimeCodeHasExpired(): void
    {
        $this->customer->update(['one_time_code_expires_at' => now()->subSecond()]);

        $this->makeRequest()
            ->assertRedirect(route('my-travel-cards.login.show'))
            ->assertSessionHasErrors(['email' => 'Sorry, your one time code has expired, please try again.']);

        $this->assertGuest('customers');
    }

    #[Test]
    public function itLogsTheCustomerInWithTheCorrectCode(): void
    {
        $this->makeRequest();

        $this->assertAuthenticatedAs($this->customer, 'customers');
    }

    #[Test]
    public function itRemembersTheCustomer(): void
    {
        $this->makeRequest()->assertCookie(Auth::guard('customers')->getRecallerName());

        $this->customer->refresh();

        $this->assertNotNull($this->customer->getRememberToken());
    }

    #[Test]
    public function itRemembersTheCustomerForSevenDays(): void
    {
        $response = $this->makeRequest();

        $recaller = collect($response->headers->getCookies())
            ->first(fn (Cookie $cookie) => $cookie->getName() === Auth::guard('customers')->getRecallerName());

        $this->assertSame(now()->addDays(7)->getTimestamp(), $recaller->getExpiresTime());
    }

    #[Test]
    public function itClearsTheOneTimeCodeAndItsExpiry(): void
    {
        $this->makeRequest();

        $this->customer->refresh();

        $this->assertNull($this->customer->one_time_code);
        $this->assertNull($this->customer->one_time_code_expires_at);
    }

    #[Test]
    public function itRedirectsToMyTravelCards(): void
    {
        $this->makeRequest()->assertRedirect(route('my-travel-cards.index'));
    }

    #[Test]
    public function itForgetsTheEmailCookie(): void
    {
        $this->makeRequest()->assertCookieExpired('my_travel_cards_email');
    }

    protected function makeRequest(array $params = [], ?string $email = null): TestResponse
    {
        if ( ! array_key_exists('code', $params)) {
            $params['code'] = '123456';
        }

        return $this
            ->withCookie('my_travel_cards_email', Crypt::encryptString($email ?? $this->customer->email))
            ->post(
                route('my-travel-cards.login.confirm.store'),
                MyTravelCardsConfirmOneTimeCodeRequestFactory::new()->create($params),
            );
    }
}
