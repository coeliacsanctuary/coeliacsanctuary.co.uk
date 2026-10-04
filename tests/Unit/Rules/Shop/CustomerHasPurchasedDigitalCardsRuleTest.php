<?php

declare(strict_types=1);

namespace Tests\Unit\Rules\Shop;

use App\Actions\Shop\MyTravelCards\CheckIfCustomerCanAccessMyTravelCardsAction;
use App\Models\Shop\ShopCustomer;
use App\Rules\Shop\CustomerHasPurchasedDigitalCardsRule;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerHasPurchasedDigitalCardsRuleTest extends TestCase
{
    protected CustomerHasPurchasedDigitalCardsRule $rule;

    protected ShopCustomer $customer;

    /** @var array<int, string> */
    protected array $failures = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new CustomerHasPurchasedDigitalCardsRule;
        $this->customer = $this->create(ShopCustomer::class, ['email' => 'foo@bar.com']);
        $this->failures = [];
    }

    protected function failureCallback(): Closure
    {
        return function (string $message): void {
            $this->failures[] = $message;
        };
    }

    #[Test]
    public function itPassesIfTheCustomerCanAccessMyTravelCards(): void
    {
        $this->expectAction(
            CheckIfCustomerCanAccessMyTravelCardsAction::class,
            [fn (ShopCustomer $customer) => $customer->is($this->customer)],
            return: true,
        );

        $this->rule->validate('email', 'foo@bar.com', $this->failureCallback());

        $this->assertSame([], $this->failures);
    }

    #[Test]
    public function itFailsIfTheCustomerCantAccessMyTravelCards(): void
    {
        $this->expectAction(
            CheckIfCustomerCanAccessMyTravelCardsAction::class,
            then: fn ($mock) => $mock->andReturnFalse(),
        );

        $this->rule->validate('email', 'foo@bar.com', $this->failureCallback());

        $this->assertSame(['Sorry, you must have purchased a digital card to access My Travel Cards.'], $this->failures);
    }

    #[Test]
    public function itResolvesTheCustomerFromTheEmailBeingValidated(): void
    {
        /** @var ShopCustomer $otherCustomer */
        $otherCustomer = $this->create(ShopCustomer::class, ['email' => 'bar@foo.com']);

        $this->expectAction(
            CheckIfCustomerCanAccessMyTravelCardsAction::class,
            [fn (ShopCustomer $customer) => $customer->is($otherCustomer)],
            return: true,
        );

        $this->rule->validate('email', 'bar@foo.com', $this->failureCallback());

        $this->assertSame([], $this->failures);
    }

    #[Test]
    public function itThrowsAnExceptionIfThereIsntACustomerWithTheGivenEmail(): void
    {
        $this->dontExpectAction(CheckIfCustomerCanAccessMyTravelCardsAction::class);

        $this->expectException(ModelNotFoundException::class);

        $this->rule->validate('email', 'nobody@bar.com', $this->failureCallback());
    }
}
