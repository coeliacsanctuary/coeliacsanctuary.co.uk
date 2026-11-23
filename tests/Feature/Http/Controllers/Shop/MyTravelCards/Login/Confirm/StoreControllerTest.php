<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards\Login\Confirm;

use App\Actions\Shop\MyTravelCards\LoginCustomerAction;
use App\Models\Shop\ShopCustomer;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\RequestFactories\MyTravelCardsConfirmOneTimeCodeRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected ShopCustomer $customer;

    protected function setUp(): void
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
        $this->dontExpectAction(LoginCustomerAction::class);

        $this->post(route('my-travel-cards.login.confirm.store'), MyTravelCardsConfirmOneTimeCodeRequestFactory::new()->create())
            ->assertRedirect(route('my-travel-cards.login.show'));
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
        $this->dontExpectAction(LoginCustomerAction::class);

        $this->makeRequest(['code' => '654321'])
            ->assertSessionHasErrors(['code' => 'The one time code you entered is incorrect, please try again.']);
    }

    #[Test]
    public function itRedirectsToTheLoginPageWhenTheOneTimeCodeHasExpired(): void
    {
        $this->customer->update(['one_time_code_expires_at' => now()->subSecond()]);

        $this->dontExpectAction(LoginCustomerAction::class);

        $this->makeRequest()
            ->assertRedirect(route('my-travel-cards.login.show'))
            ->assertSessionHasErrors(['email' => 'Sorry, your one time code has expired, please try again.']);
    }

    #[Test]
    public function itCallsTheLoginCustomerActionWithTheCorrectCode(): void
    {
        $this->expectAction(LoginCustomerAction::class, [fn (ShopCustomer $customer) => $customer->is($this->customer)]);

        $this->makeRequest();
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
