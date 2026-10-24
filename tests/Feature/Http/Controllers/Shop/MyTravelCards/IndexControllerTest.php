<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards;

use App\Enums\Shop\DigitalCardType;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopOrder $order;

    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);
        $this->order = $this->orderFor($this->customer);

        /** @var ShopProduct $product */
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        $this->product = $product;
    }

    #[Test]
    public function itRedirectsGuestsToTheLoginPage(): void
    {
        $this->get(route('my-travel-cards.index'))->assertRedirect(route('my-travel-cards.login.show'));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itRendersTheMyTravelCardsPage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->component('Shop/MyTravelCards/Index'));
    }

    #[Test]
    public function itSetsThePageTitle(): void
    {
        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page->where('meta.title', 'My Travel Cards')->etc()
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
    public function itReturnsNoCardsWhenTheCustomerHasntBoughtAny(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->has('cards', 0)->etc());
    }

    #[Test]
    public function itReturnsTheCustomersPurchasedCards(): void
    {
        $this->purchase($this->card([
            'type_id' => DigitalCardType::STANDARD,
            'title' => 'French Travel Card',
            'slug' => 'french-travel-card',
            'language' => 'french',
        ]));

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->has('cards', 1)
                ->where('cards.0.title', 'French Travel Card')
                ->where('cards.0.slug', 'french-travel-card')
                ->where('cards.0.language', 'french')
                ->etc()
        );
    }

    #[Test]
    public function itDoesntReturnAnotherCustomersCards(): void
    {
        /** @var ShopCustomer $otherCustomer */
        $otherCustomer = $this->create(ShopCustomer::class);

        $this->purchase($this->card(), $this->orderFor($otherCustomer));

        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->has('cards', 0)->etc());
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
    public function itDoesntReturnCardsFromOrdersThatArentPaid(OrderState $state): void
    {
        $this->purchase($this->card(), $this->orderFor($this->customer, $state));

        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->has('cards', 0)->etc());
    }

    public static function fulfilledOrderStateDataProvider(): array
    {
        return [
            'paid' => [OrderState::PAID],
            'ready' => [OrderState::READY],
            'shipped' => [OrderState::SHIPPED],
        ];
    }

    #[Test]
    #[DataProvider('fulfilledOrderStateDataProvider')]
    public function itReturnsCardsFromPaidReadyAndShippedOrders(OrderState $state): void
    {
        $this->purchase($this->card(), $this->orderFor($this->customer, $state));

        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->has('cards', 1)->etc());
    }

    #[Test]
    public function itOnlyReturnsACardOnceWhenItHasBeenBoughtMoreThanOnce(): void
    {
        $card = $this->card(['title' => 'French Travel Card']);

        $this->purchase($card);
        $this->purchase($card, $this->orderFor($this->customer));

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->has('cards', 1)
                ->where('cards.0.title', 'French Travel Card')
                ->etc()
        );
    }

    #[Test]
    public function itSortsTheCardsByTitle(): void
    {
        $this->purchase($this->card(['title' => 'Spanish Travel Card', 'language' => 'spanish']));
        $this->purchase($this->card(['title' => 'French Travel Card', 'language' => 'french']));
        $this->purchase($this->card(['title' => 'German Travel Card', 'language' => 'german']));

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->where('cards.0.title', 'French Travel Card')
                ->where('cards.1.title', 'German Travel Card')
                ->where('cards.2.title', 'Spanish Travel Card')
                ->etc()
        );
    }

    #[Test]
    public function itReturnsTheCountriesForTheCardHighestScoringFirst(): void
    {
        $this->purchase($this->card(['language' => 'french'], withProduct: true));

        $this->attachTerm('belgium', score: 5);
        $this->attachTerm('france', score: 10);

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->has('cards.0.countries', 2)
                ->where('cards.0.countries.0.country', 'France')
                ->where('cards.0.countries.0.code', 'fr')
                ->where('cards.0.countries.1.country', 'Belgium')
                ->where('cards.0.countries.1.code', 'be')
                ->where('cards.0.flag', 'fr')
                ->etc()
        );
    }

    #[Test]
    public function itDoesntReturnCountriesForAnotherCardsLanguage(): void
    {
        $this->purchase($this->card(['language' => 'french'], withProduct: true));

        $this->attachTerm('france', language: 'french');
        $this->attachTerm('spain', language: 'spanish');

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->has('cards.0.countries', 1)
                ->where('cards.0.countries.0.country', 'France')
                ->etc()
        );
    }

    #[Test]
    public function itDoesntReturnTermsThatArentCountries(): void
    {
        $this->purchase($this->card(['language' => 'french'], withProduct: true));

        $this->attachTerm('france');
        $this->attachTerm('french', type: 'language');

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->has('cards.0.countries', 1)
                ->where('cards.0.countries.0.country', 'France')
                ->etc()
        );
    }

    #[Test]
    public function itDoesntReturnCountriesThatArentShownOnTheProductPage(): void
    {
        $this->purchase($this->card(['language' => 'french'], withProduct: true));

        $this->attachTerm('france');
        $this->attachTerm('belgium', showOnProductPage: false);

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->has('cards.0.countries', 1)
                ->where('cards.0.countries.0.country', 'France')
                ->etc()
        );
    }

    #[Test]
    public function itDoesntReturnCountriesOrAFlagWhenTheCardHasNoProduct(): void
    {
        $this->purchase($this->card());

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->has('cards', 1)
                ->missing('cards.0.countries')
                ->missing('cards.0.flag')
                ->etc()
        );
    }

    protected function makeRequest(): TestResponse
    {
        return $this->actingAs($this->customer, 'customers')->get(route('my-travel-cards.index'));
    }

    protected function orderFor(ShopCustomer $customer, OrderState $state = OrderState::PAID): ShopOrder
    {
        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->forCustomer($customer)->create(['state_id' => $state]);

        return $order;
    }

    protected function card(array $attributes = [], bool $withProduct = false): ShopDigitalCard
    {
        $factory = $this->build(ShopDigitalCard::class);

        if ($withProduct) {
            $factory = $factory->forProduct($this->product);
        }

        /** @var ShopDigitalCard $card */
        $card = $factory->create($attributes);

        return $card;
    }

    protected function purchase(ShopDigitalCard $card, ?ShopOrder $order = null): void
    {
        $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order ?? $this->order)
            ->forCard($card)
            ->create();
    }

    protected function attachTerm(string $term, string $type = 'country', string $language = 'french', int $score = 10, bool $showOnProductPage = true): void
    {
        /** @var TravelCardSearchTerm $searchTerm */
        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => $type]);

        $this->product->travelCardSearchTerms()->attach($searchTerm, [
            'card_language' => $language,
            'card_score' => $score,
            'card_show_on_product_page' => $showOnProductPage,
        ]);
    }
}
