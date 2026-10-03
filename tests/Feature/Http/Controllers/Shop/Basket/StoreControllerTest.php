<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Basket;

use App\Actions\Shop\AddDigitalCardToBasketAction;
use App\Actions\Shop\AddProductToBasketAction;
use App\Actions\Shop\ResolveBasketAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Exception;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\RequestFactories\ShopAddBasketRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected ShopDigitalCard $digitalCard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);

        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();

        /** @var ShopDigitalCard $digitalCard */
        $digitalCard = $this->create(ShopDigitalCard::class);

        $this->digitalCard = $digitalCard;
    }

    #[Test]
    public function itReturnsAValidationErrorWithAnInvalidProductId(): void
    {
        $this->makeRequest(['product_id' => null])->assertSessionHasErrors('product_id');
        $this->makeRequest(['product_id' => 'foo'])->assertSessionHasErrors('product_id');
        $this->makeRequest(['product_id' => true])->assertSessionHasErrors('product_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAProductThatDoesntExist(): void
    {
        $this->makeRequest(['product_id' => 123])->assertSessionHasErrors('product_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAProductThatHasNoLiveVariants(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->makeRequest(['product_id' => $product->id])->assertSessionHasErrors('product_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAnInvalidVariantId(): void
    {
        $this->makeRequest(['variant_id' => null])->assertSessionHasErrors('variant_id');
        $this->makeRequest(['variant_id' => 'foo'])->assertSessionHasErrors('variant_id');
        $this->makeRequest(['variant_id' => true])->assertSessionHasErrors('variant_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAVariantThatDoesntExist(): void
    {
        $this->makeRequest(['variant_id' => 123])->assertSessionHasErrors('variant_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAVariantForADifferentProduct(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->makeRequest(['variant_id' => $variant->id])->assertSessionHasErrors('variant_id');
    }

    #[Test]
    public function itReturnsAValidationErrorIfTheVariantIsntLive(): void
    {
        $this->variant->update(['live' => false]);

        $this->makeRequest()->assertSessionHasErrors('variant_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAnInvalidQuantity(): void
    {
        $this->makeRequest(['quantity' => null])->assertSessionHasErrors('quantity');
        $this->makeRequest(['quantity' => 'foo'])->assertSessionHasErrors('quantity');
        $this->makeRequest(['quantity' => true])->assertSessionHasErrors('quantity');
        $this->makeRequest(['quantity' => -1])->assertSessionHasErrors('quantity');
    }

    #[Test]
    public function itReturnsAValidationErrorIfTheProductVariantDoesntHaveTheRequestedQuantity(): void
    {
        $this->variant->update(['quantity' => 1]);

        $this->makeRequest(['quantity' => 2])->assertSessionHasErrors('quantity');
    }

    #[Test]
    public function itReturnsAValidationErrorWithADodgyScientificNumber(): void
    {
        $this->makeRequest(['quantity' => '123e4'])->assertSessionHasErrors('quantity');
    }



    #[Test]
    public function itReturnsAValidationErrorIfNeitherAProductOrADigitalCardIsGiven(): void
    {
        $this->makeDigitalRequest(['digital_card_id' => null])->assertSessionHasErrors('digital_card_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAnInvalidDigitalCardId(): void
    {
        $this->makeDigitalRequest(['digital_card_id' => 'foo'])->assertSessionHasErrors('digital_card_id');
        $this->makeDigitalRequest(['digital_card_id' => true])->assertSessionHasErrors('digital_card_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithADigitalCardThatDoesntExist(): void
    {
        $this->makeDigitalRequest(['digital_card_id' => 123])->assertSessionHasErrors('digital_card_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithADigitalCardThatIsntLive(): void
    {
        $card = $this->build(ShopDigitalCard::class)->notLive()->create();

        $this->makeDigitalRequest(['digital_card_id' => $card->id])->assertSessionHasErrors('digital_card_id');
    }

    #[Test]
    public function itDoesntNeedAVariantOrQuantityToAddADigitalCard(): void
    {
        $this->makeDigitalRequest()->assertSessionHasNoErrors();
    }

    #[Test]
    public function itDoesntNeedADigitalCardIdWhenDigitalCardIdsIsGiven(): void
    {
        $this->makeDigitalCardsRequest()->assertSessionHasNoErrors();
    }

    #[Test]
    public function itReturnsAValidationErrorIfTheDigitalCardIdsArentAnArray(): void
    {
        $this->makeDigitalCardsRequest(['digital_card_ids' => $this->digitalCard->id])
            ->assertSessionHasErrors('digital_card_ids');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAnInvalidDigitalCardIdInTheArray(): void
    {
        $this->makeDigitalCardsRequest(['digital_card_ids' => ['foo']])->assertSessionHasErrors('digital_card_ids.0');
        $this->makeDigitalCardsRequest(['digital_card_ids' => [true]])->assertSessionHasErrors('digital_card_ids.0');
    }

    #[Test]
    public function itReturnsAValidationErrorWithADigitalCardInTheArrayThatDoesntExist(): void
    {
        $this->makeDigitalCardsRequest(['digital_card_ids' => [123]])->assertSessionHasErrors('digital_card_ids.0');
    }

    #[Test]
    public function itReturnsAValidationErrorWithADigitalCardInTheArrayThatIsntLive(): void
    {
        $card = $this->build(ShopDigitalCard::class)->notLive()->create();

        $this->makeDigitalCardsRequest(['digital_card_ids' => [$card->id]])->assertSessionHasErrors('digital_card_ids.0');
    }

    #[Test]
    public function itReturnsAValidationErrorIfOneOfTheGivenDigitalCardsIsInvalid(): void
    {
        $this->makeDigitalCardsRequest(['digital_card_ids' => [$this->digitalCard->id, 123]])
            ->assertSessionHasErrors('digital_card_ids.1');
    }

    #[Test]
    public function itCallsTheResolveBasketAction(): void
    {
        $this->expectAction(ResolveBasketAction::class);

        $this->makeRequest();
    }

    #[Test]
    public function itPassesTheCookieBasketTokenToTheResolveBasketAction(): void
    {
        $order = $this->create(ShopOrder::class);

        $this->expectAction(ResolveBasketAction::class, [$order->token]);
        $this->withCookie('basket_token', $order->token)->makeRequest();

        $this->assertDatabaseCount(ShopOrder::class, 1);
    }

    #[Test]
    public function itReturnsTheBasketTokenInACookie(): void
    {
        $request = $this->makeRequest();

        $order = ShopOrder::query()->first();

        $request->assertCookie('basket_token', $order->token);
    }

    #[Test]
    public function itReturnsTheBasketTokenInACookieWhenAddingADigitalCard(): void
    {
        $request = $this->makeDigitalRequest();

        $order = ShopOrder::query()->first();

        $request->assertCookie('basket_token', $order->token);
    }

    #[Test]
    public function itCallsTheAddProductToBasketAction(): void
    {
        $this->expectAction(AddProductToBasketAction::class, [function ($order, $product, $variant, $quantity, $shouldIncreaseQuantity) {
            $this->assertTrue($this->product->is($product));
            $this->assertTrue($this->variant->is($variant));
            $this->assertEquals(1, $quantity);
            $this->assertTrue($shouldIncreaseQuantity);

            return true;
        }]);

        $this->makeRequest();
    }

    #[Test]
    public function itTellsTheAddProductToBasketActionNotToIncreaseTheQuantityWhenADigitalCardIsInTheSameRequest(): void
    {
        $orderItem = $this->create(ShopOrderItem::class);

        $this->expectAction(AddProductToBasketAction::class, [function ($order, $product, $variant, $quantity, $shouldIncreaseQuantity) {
            $this->assertFalse($shouldIncreaseQuantity);

            return true;
        }], return: $orderItem);

        $this->expectAction(AddDigitalCardToBasketAction::class);

        $this->makeRequest(['digital_card_id' => $this->digitalCard->id])->assertSessionHasNoErrors();
    }

    #[Test]
    public function itDoesntCallTheAddDigitalCardToBasketActionForAProduct(): void
    {
        $this->dontExpectAction(AddDigitalCardToBasketAction::class);

        $this->makeRequest();
    }

    #[Test]
    public function itCallsTheAddDigitalCardToBasketAction(): void
    {
        $this->expectAction(AddDigitalCardToBasketAction::class, [function ($order, $card, $physicalItem) {
            $this->assertTrue(ShopOrder::query()->first()->is($order));
            $this->assertTrue($this->digitalCard->is($card));
            $this->assertNull($physicalItem);

            return true;
        }]);

        $this->makeDigitalRequest();
    }

    #[Test]
    public function itDoesntCallTheAddProductToBasketActionForADigitalCard(): void
    {
        $this->dontExpectAction(AddProductToBasketAction::class);

        $this->makeDigitalRequest();
    }

    #[Test]
    public function itPassesTheCreatedOrderItemToTheAddDigitalCardToBasketActionWhenAProductIsAddedInTheSameRequest(): void
    {
        $orderItem = $this->create(ShopOrderItem::class);

        $this->expectAction(AddProductToBasketAction::class, return: $orderItem);

        $this->expectAction(AddDigitalCardToBasketAction::class, [function ($order, $card, $physicalItem) use ($orderItem) {
            $this->assertTrue($this->digitalCard->is($card));
            $this->assertTrue($orderItem->is($physicalItem));

            return true;
        }]);

        $this->makeRequest(['digital_card_id' => $this->digitalCard->id])->assertSessionHasNoErrors();
    }

    #[Test]
    public function itCallsTheAddDigitalCardToBasketActionForEachGivenCard(): void
    {
        /** @var ShopDigitalCard $secondCard */
        $secondCard = $this->create(ShopDigitalCard::class);

        $cards = [];

        $this->expectAction(AddDigitalCardToBasketAction::class, [function ($order, $card, $physicalItem) use (&$cards) {
            $cards[] = $card->id;

            $this->assertNull($physicalItem);

            return true;
        }], once: false, then: fn ($mock) => $mock->twice());

        $this->makeDigitalCardsRequest(['digital_card_ids' => [$this->digitalCard->id, $secondCard->id]])
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$this->digitalCard->id, $secondCard->id], $cards);
    }

    #[Test]
    public function itPassesTheSamePhysicalOrderItemToEveryDigitalCard(): void
    {
        /** @var ShopDigitalCard $secondCard */
        $secondCard = $this->create(ShopDigitalCard::class);

        $orderItem = $this->create(ShopOrderItem::class);

        $this->expectAction(AddProductToBasketAction::class, return: $orderItem);

        $this->expectAction(AddDigitalCardToBasketAction::class, [function ($order, $card, $physicalItem) use ($orderItem) {
            $this->assertTrue($orderItem->is($physicalItem));

            return true;
        }], once: false, then: fn ($mock) => $mock->twice());

        $this->makeRequest(['digital_card_ids' => [$this->digitalCard->id, $secondCard->id]])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function itOnlyAddsADigitalCardOnceIfTheSameIdIsGivenTwice(): void
    {
        $this->expectAction(AddDigitalCardToBasketAction::class, [function ($order, $card) {
            $this->assertTrue($this->digitalCard->is($card));

            return true;
        }]);

        $this->makeDigitalCardsRequest(['digital_card_ids' => [$this->digitalCard->id, $this->digitalCard->id]])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function itAcceptsBothDigitalCardIdAndDigitalCardIdsInOneRequest(): void
    {
        /** @var ShopDigitalCard $secondCard */
        $secondCard = $this->create(ShopDigitalCard::class);

        $cards = [];

        $this->expectAction(AddDigitalCardToBasketAction::class, [function ($order, $card) use (&$cards) {
            $cards[] = $card->id;

            return true;
        }], once: false, then: fn ($mock) => $mock->twice());

        $this->makeDigitalCardsRequest([
            'digital_card_ids' => [$secondCard->id],
            'digital_card_id' => $this->digitalCard->id,
        ])->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$secondCard->id, $this->digitalCard->id], $cards);
    }

    #[Test]
    public function itUpdatesTheBasketUpdatedAtTime(): void
    {
        TestTime::freeze();
        $order = $this->create(ShopOrder::class);

        TestTime::addMinutes(5);
        $this->withCookie('basket_token', $order->token)->makeRequest();

        $this->assertTrue($order->refresh()->updated_at->isSameSecond(now()));
    }

    #[Test]
    public function itRedirectsBackOnSuccess(): void
    {
        $this->from(route('shop.category', ['category' => $this->product->categories()->first()->slug]))
            ->makeRequest()
            ->assertRedirectToRoute('shop.category', ['category' => $this->product->categories()->first()->slug]);
    }

    #[Test]
    public function itRollsBackAndReturnsAValidationErrorIfTheProductCantBeAddedToTheBasket(): void
    {
        $this->expectAction(AddProductToBasketAction::class, then: fn ($mock) => $mock->andThrow(new Exception('Something went wrong')));

        $this->makeRequest()->assertSessionHasErrors(['product_id' => 'There was an error adding the item to your basket.']);

        $this->assertDatabaseEmpty(ShopOrder::class);
    }

    #[Test]
    public function itRollsBackAndReturnsAValidationErrorIfTheDigitalCardCantBeAddedToTheBasket(): void
    {
        $this->expectAction(AddDigitalCardToBasketAction::class, then: fn ($mock) => $mock->andThrow(new Exception('Something went wrong')));

        $this->makeDigitalRequest()->assertSessionHasErrors(['product_id' => 'There was an error adding the item to your basket.']);

        $this->assertDatabaseEmpty(ShopOrder::class);
    }

    protected function makeRequest(array $data = []): TestResponse
    {
        $data = array_merge([
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
        ], $data);

        return $this->put(route('shop.basket.add'), ShopAddBasketRequestFactory::new()->create($data));
    }

    protected function makeDigitalRequest(array $data = []): TestResponse
    {
        $data = array_merge(['digital_card_id' => $this->digitalCard->id], $data);

        return $this->put(
            route('shop.basket.add'),
            ShopAddBasketRequestFactory::new()->without(['product_id', 'variant_id', 'quantity'])->create($data),
        );
    }

    protected function makeDigitalCardsRequest(array $data = []): TestResponse
    {
        $data = array_merge(['digital_card_ids' => [$this->digitalCard->id]], $data);

        return $this->put(
            route('shop.basket.add'),
            ShopAddBasketRequestFactory::new()->without(['product_id', 'variant_id', 'quantity'])->create($data),
        );
    }
}
