<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Order;

use App\Actions\Shop\ApplyDiscountCodeAction;
use App\Actions\Shop\Checkout\CreateCustomerAction;
use App\Actions\Shop\Checkout\CreateShippingAddressAction;
use App\Actions\Shop\ResolveBasketAction;
use App\DataObjects\Shop\PendingOrderCustomerDetails;
use App\DataObjects\Shop\PendingOrderShippingAddressDetails;
use App\Enums\Shop\OrderState;
use App\Enums\Shop\PostageArea;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopDiscountCodesUsed;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPayment;
use App\Models\Shop\ShopPostageCountry;
use App\Models\Shop\ShopPostagePrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\ShopShippingAddress;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\ShopCompleteOrderRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected ShopOrder $basket;

    protected ShopOrderItem $item;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);

        $this->basket = $this->build(ShopOrder::class)->asBasket()->create();
        $this->create(ShopPostageCountry::class, ['id' => $this->basket->postage_country_id, 'country' => 'UK']);

        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();

        $this->item = $this->create(ShopOrderItem::class, [
            'order_id' => $this->basket->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_price' => 200,
        ]);

        $this->create(ShopPostagePrice::class, [
            'postage_country_area_id' => PostageArea::UK->value,
            'shipping_method_id' => $this->product->shipping_method_id,
            'max_weight' => 500,
            'price' => 100,
        ]);
    }

    #[Test]
    public function itReturnsAnErrorIfTheBasketDoesntExist(): void
    {
        $this->basket->delete();

        $this->makeRequest()->assertSessionHasErrors('basket');
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itReturnsAnErrorIfTheBasketIsntABasketState(callable $before): void
    {
        $before($this);

        $this->basket->update(['state_id' => OrderState::PENDING]);

        $this->makeRequest()->assertSessionHasErrors('basket');
    }

    #[Test]
    #[DataProvider('validationDataProvider')]
    public function itHandlesFieldValidationRulesForNormalOrders(array $data, string|array $key): void
    {
        $this->makeRequest($data)->assertSessionHasErrors($key);
    }

    #[Test]
    #[DataProvider('digitalOnlyValidationDataProvider')]
    public function itHandlesFieldValidationRulesForDigitalOnlyOrders(array $data, string|array $key): void
    {
        $this->basket->update(['is_digital_only' => true]);

        $this->makeRequest($data)->assertSessionHasErrors($key);
    }

    #[Test]
    public function itDoesntReturnAValidationErrorForMissingShippingDataForDigitalOrders(): void
    {
        $this->basket->update(['is_digital_only' => true]);

        $this->makeRequest(['contact' => null])->assertSessionDoesntHaveErrors('shipping');
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itCallsTheResolveBasketAction(callable $before): void
    {
        $before($this);

        $this->expectAction(ResolveBasketAction::class, once: false, return: $this->basket);

        $this->makeRequest()->assertCreated();
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itCallsTheCreateUserAction(callable $before): void
    {
        $before($this);

        $this->expectAction(CreateCustomerAction::class, [PendingOrderCustomerDetails::class], return: $this->create(ShopCustomer::class));

        $this->makeRequest()->assertCreated();
    }

    #[Test]
    public function itCallsTheCreateUserAddressAction(): void
    {
        $this->expectAction(CreateShippingAddressAction::class, [ShopCustomer::class, PendingOrderShippingAddressDetails::class], return: $this->create(ShopShippingAddress::class));

        $this->makeRequest()->assertCreated();
    }

    #[Test]
    public function itDoesntCallTheCreateUserAddressActionForDigitalOnlyOrders(): void
    {
        $this->dontExpectAction(CreateShippingAddressAction::class);

        $this->basket->update(['is_digital_only' => true]);

        $this->makeRequest()->assertCreated();
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itCallsTheApplyDiscountCodeActionIfADiscountCodeIsPresentInTheSession(callable $before): void
    {
        $before($this);

        $this->expectAction(ApplyDiscountCodeAction::class);

        $this->create(ShopDiscountCode::class, ['code' => 'foobar']);

        $this->makeRequest(session: ['discountCode' => app(Encrypter::class)->encrypt('foobar')]);
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itCreatesADiscountCodeUsedRecordIfADiscountCodeIsPresent(callable $before): void
    {
        $before($this);

        $this->create(ShopDiscountCode::class, ['code' => 'foobar']);

        $this->assertDatabaseEmpty(ShopDiscountCodesUsed::class);

        $this->makeRequest(session: ['discountCode' => app(Encrypter::class)->encrypt('foobar')]);

        $this->assertDatabaseCount(ShopDiscountCodesUsed::class, 1);
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itCreatesAShopPaymentRecord(callable $before): void
    {
        $before($this);

        $this->assertDatabaseEmpty(ShopPayment::class);

        $this->makeRequest()->assertCreated();

        $this->assertDatabaseCount(ShopPayment::class, 1);
        $this->assertNotNull($this->basket->refresh()->payment);
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itUsesAnExistingPaymentRecord(callable $before): void
    {
        $before($this);

        $payment = $this->create(ShopPayment::class, ['order_id' => $this->basket->id, 'total' => 1]);

        $this->makeRequest()->assertCreated();

        $this->assertDatabaseCount(ShopPayment::class, 1);
        $this->assertNotEquals(1, $payment->refresh()->total);
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itUpdatesTheOrder(callable $before): void
    {
        $before($this);

        $this->assertNull($this->basket->customer_id);
        $this->assertNull($this->basket->shipping_address_id);
        $this->assertNull($this->basket->order_key);
        $this->assertEquals(OrderState::BASKET, $this->basket->state_id);

        $this->makeRequest()->assertCreated();

        $this->basket->refresh();

        $this->assertNotNull($this->basket->customer_id);

        if ( ! $this->basket->is_digital_only) {
            $this->assertNotNull($this->basket->shipping_address_id);
        } else {
            $this->assertNull($this->basket->shipping_address_id);
        }

        $this->assertNotNull($this->basket->order_key);
        $this->assertEquals(OrderState::PENDING, $this->basket->state_id);
        $this->assertNotNull($this->basket->order_key);
        $this->assertEquals(OrderState::PENDING, $this->basket->state_id);
    }

    #[Test]
    #[DataProvider('orderStateDataProvider')]
    public function itCreatesAKeyThatIsEightDigitsLong(callable $before): void
    {
        $before($this);

        $this->makeRequest()->assertCreated();

        $this->basket->refresh();

        $this->assertSame(Str::length($this->basket->order_key), 8);
    }

    protected function makeRequest(array $data = [], array $session = []): TestResponse
    {
        $payload = ShopCompleteOrderRequestFactory::new($data);

        if ($this->basket->is_digital_only) {
            $payload->digitalOnly();
        }

        return $this
            ->withCookie('basket_token', $this->basket->token)
            ->withSession($session)
            ->post(route('shop.order.complete'), $payload->create());
    }

    public static function validationDataProvider(): array
    {
        return [
            // contact details
            'missing contact object' => [['contact' => null], 'contact'],

            // contact name
            'missing contact name' => [['contact.name' => null], 'contact.name'],
            'contact name is numeric' => [['contact.name' => 123], 'contact.name'],
            'contact name is bool' => [['contact.name' => true], 'contact.name'],

            // contact email
            'missing contact email' => [['contact.email' => null], 'contact.email'],
            'contact email is numeric' => [['contact.email' => 123], 'contact.email'],
            'contact email is bool' => [['contact.email' => true], 'contact.email'],
            'contact email is not an email address' => [['contact.email' => 'foobar'], 'contact.email'],

            // contact email confirmation
            'missing contact email confirmation' => [['contact.email_confirmation' => null], ['contact.email']],
            'contact email confirmation is numeric' => [['contact.email_confirmation' => 123], ['contact.email']],
            'contact email confirmation is bool' => [['contact.email_confirmation' => true], ['contact.email']],
            'contact email confirmation is not an email address' => [['contact.email_confirmation' => 'foobar'], ['contact.email']],
            'contact email and email confirmation do not match' => [['contact.email' => 'foo@bar.com', 'contact.email_confirmation' => 'bar@baz.com'], ['contact.email']],

            // shipping details
            'missing shipping object' => [['shipping' => null], 'shipping'],

            // shipping address 1
            'missing shipping address line 1' => [['shipping.address_1' => null], 'shipping.address_1'],
            'shipping address line 1 is numeric' => [['shipping.address_1' => 123], 'shipping.address_1'],
            'shipping address line 1 is bool' => [['shipping.address_1' => true], 'shipping.address_1'],

            // shipping address 2
            'shipping address line 2 is numeric' => [['shipping.address_2' => 123], 'shipping.address_2'],
            'shipping address line 2 is bool' => [['shipping.address_2' => true], 'shipping.address_2'],

            // shipping address 3
            'shipping address line 3 is numeric' => [['shipping.address_3' => 123], 'shipping.address_3'],
            'shipping address line 3 is bool' => [['shipping.address_3' => true], 'shipping.address_3'],

            // shipping town
            'missing shipping town' => [['shipping.town' => null], 'shipping.town'],
            'shipping town is numeric' => [['shipping.town' => 123], 'shipping.town'],
            'shipping town is bool' => [['shipping.town' => true], 'shipping.town'],

            // shipping address 3
            'shipping county is numeric' => [['shipping.county' => 123], 'shipping.county'],
            'shipping county is bool' => [['shipping.county' => true], 'shipping.county'],

            // postcode
            'missing shipping postcode' => [['shipping.postcode' => null], 'shipping.postcode'],
            'shipping postcode is numeric' => [['shipping.postcode' => 123], 'shipping.postcode'],
            'shipping postcode is bool' => [['shipping.postcode' => true], 'shipping.postcode'],
            'shipping postcode is invalid' => [['shipping.postcode' => 'foo'], 'shipping.postcode'],
        ];
    }

    public static function digitalOnlyValidationDataProvider(): array
    {
        return [
            // contact details
            'missing contact object' => [['contact' => null], 'contact'],

            // contact name
            'missing contact name' => [['contact.name' => null], 'contact.name'],
            'contact name is numeric' => [['contact.name' => 123], 'contact.name'],
            'contact name is bool' => [['contact.name' => true], 'contact.name'],

            // contact email
            'missing contact email' => [['contact.email' => null], 'contact.email'],
            'contact email is numeric' => [['contact.email' => 123], 'contact.email'],
            'contact email is bool' => [['contact.email' => true], 'contact.email'],
            'contact email is not an email address' => [['contact.email' => 'foobar'], 'contact.email'],

            // contact email confirmation
            'missing contact email confirmation' => [['contact.email_confirmation' => null], ['contact.email']],
            'contact email confirmation is numeric' => [['contact.email_confirmation' => 123], ['contact.email']],
            'contact email confirmation is bool' => [['contact.email_confirmation' => true], ['contact.email']],
            'contact email confirmation is not an email address' => [['contact.email_confirmation' => 'foobar'], ['contact.email']],
            'contact email and email confirmation do not match' => [['contact.email' => 'foo@bar.com', 'contact.email_confirmation' => 'bar@baz.com'], ['contact.email']],
        ];
    }

    public static function orderStateDataProvider(): array
    {
        return [
            'normal' => [fn () => null],
            'digital' => [fn (self $test) => $test->basket->update(['is_digital_only' => true])],
        ];
    }
}
