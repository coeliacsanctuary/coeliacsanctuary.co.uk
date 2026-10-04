<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\MyTravelCards;

use App\Actions\Shop\MyTravelCards\LoginCustomerAction;
use App\Models\Shop\ShopCustomer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Tests\TestCase;

class LoginCustomerActionTest extends TestCase
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
    public function itLogsTheCustomerInOnTheCustomersGuard(): void
    {
        $this->callAction(LoginCustomerAction::class, $this->customer);

        $this->assertAuthenticatedAs($this->customer, 'customers');
    }

    #[Test]
    public function itRemembersTheCustomer(): void
    {
        $this->callAction(LoginCustomerAction::class, $this->customer);

        $this->assertNotNull($this->customer->refresh()->getRememberToken());

        $this->assertNotNull($this->getRecallerCookie());
    }

    #[Test]
    public function itRemembersTheCustomerForSevenDays(): void
    {
        $this->callAction(LoginCustomerAction::class, $this->customer);

        $this->assertSame(now()->addDays(7)->getTimestamp(), $this->getRecallerCookie()->getExpiresTime());
    }

    #[Test]
    public function itClearsTheOneTimeCodeAndItsExpiry(): void
    {
        $this->callAction(LoginCustomerAction::class, $this->customer);

        $this->customer->refresh();

        $this->assertNull($this->customer->one_time_code);
        $this->assertNull($this->customer->one_time_code_expires_at);
    }

    protected function getRecallerCookie(): ?SymfonyCookie
    {
        return collect(Cookie::getQueuedCookies())
            ->first(fn (SymfonyCookie $cookie) => $cookie->getName() === Auth::guard('customers')->getRecallerName());
    }
}
