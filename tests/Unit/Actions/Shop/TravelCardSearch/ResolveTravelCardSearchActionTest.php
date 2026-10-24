<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\ResolveTravelCardSearchAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResolveTravelCardSearchActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 2, 1);
    }

    protected function product(int $index): ShopProduct
    {
        /** @var ShopProduct $product */
        $product = ShopProduct::query()->orderBy('id')->get()->get($index);

        return $product;
    }

    protected function term(string $term, string $type = 'country'): TravelCardSearchTerm
    {
        /** @var TravelCardSearchTerm $searchTerm */
        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => $type]);

        return $searchTerm;
    }

    /** @return array{term: string, destinations: mixed, covers_all: mixed}|null */
    protected function resolve(string $searchString): ?array
    {
        return $this->callAction(ResolveTravelCardSearchAction::class, $searchString);
    }

    #[Test]
    public function itResolvesASingleTerm(): void
    {
        $this->term('Spain')->products()->attach($this->product(0));

        $result = $this->resolve('Spain');

        $this->assertNotNull($result);
        $this->assertEquals('Spain', $result['term']);
        $this->assertCount(1, $result['destinations']);
        $this->assertEquals('Spain', $result['destinations'][0]['term']);
        $this->assertEquals('country', $result['destinations'][0]['type']);
    }

    #[Test]
    public function itReturnsNullWhenNothingMatches(): void
    {
        $this->term('Spain')->products()->attach($this->product(0));

        $this->assertNull($this->resolve('asdfgh'));
    }

    #[Test]
    public function itReturnsNullForAnEmptySearch(): void
    {
        $this->assertNull($this->resolve(''));
        $this->assertNull($this->resolve('   '));
    }

    #[Test]
    public function itPrefersAnExactMatchOverALongerContainingTerm(): void
    {
        $equatorial = $this->term('Equatorial Guinea');
        $guinea = $this->term('Guinea');

        $equatorial->products()->attach($this->product(0));
        $guinea->products()->attach($this->product(1));

        $result = $this->resolve('Guinea');

        $this->assertNotNull($result);
        $this->assertCount(1, $result['destinations']);
        $this->assertEquals('Guinea', $result['destinations'][0]['term']);
    }

    #[Test]
    public function itMatchesRegardlessOfTheCaseTyped(): void
    {
        $this->term('spain')->products()->attach($this->product(0));

        $result = $this->resolve('SPAIN');

        $this->assertNotNull($result);
        $this->assertEquals('Spain', $result['destinations'][0]['term']);
    }

    #[Test]
    public function itLeavesTermsThatAlreadyCarryACapitalAlone(): void
    {
        $this->term('UAE')->products()->attach($this->product(0));

        $result = $this->resolve('uae');

        $this->assertNotNull($result);
        $this->assertEquals('UAE', $result['destinations'][0]['term']);
    }

    #[Test]
    public function itResolvesTheWholeStringBeforeSplittingIt(): void
    {
        $this->term('Trinidad and Tobago')->products()->attach($this->product(0));
        $this->term('Trinidad')->products()->attach($this->product(1));

        $result = $this->resolve('Trinidad and Tobago');

        $this->assertNotNull($result);
        $this->assertCount(1, $result['destinations']);
        $this->assertEquals('Trinidad and Tobago', $result['destinations'][0]['term']);
    }

    #[Test]
    #[DataProvider('separatorProvider')]
    public function itSplitsAMultiDestinationSearch(string $searchString): void
    {
        $this->term('Spain')->products()->attach($this->product(0));
        $this->term('France')->products()->attach($this->product(1));

        $result = $this->resolve($searchString);

        $this->assertNotNull($result);
        $this->assertCount(2, $result['destinations']);
        $this->assertEquals(['Spain', 'France'], collect($result['destinations'])->pluck('term')->all());
    }

    /** @return array<string, array<int, string>> */
    public static function separatorProvider(): array
    {
        return [
            'and' => ['Spain and France'],
            'ampersand' => ['Spain & France'],
            'plus' => ['Spain + France'],
            'slash' => ['Spain/France'],
            'comma' => ['Spain, France'],
        ];
    }

    #[Test]
    public function itReturnsNullWhenOnlyPartOfAMultiDestinationSearchResolves(): void
    {
        $this->term('Spain')->products()->attach($this->product(0));

        $this->assertNull($this->resolve('Spain and Narnia'));
    }

    #[Test]
    public function itReturnsTheProductsCoveringEveryDestination(): void
    {
        $spain = $this->term('Spain');
        $italy = $this->term('Italy');

        $spain->products()->attach($this->product(0));
        $italy->products()->attach($this->product(0));
        $italy->products()->attach($this->product(1));

        $result = $this->resolve('Spain and Italy');

        $this->assertNotNull($result);
        $this->assertCount(1, $result['covers_all']->collection);
        $this->assertEquals($this->product(0)->id, $result['covers_all']->collection->first()->id);
    }

    #[Test]
    public function itReturnsNoCoveringProductsWhenNoSingleCardSpansThem(): void
    {
        $this->term('Spain')->products()->attach($this->product(0));
        $this->term('France')->products()->attach($this->product(1));

        $result = $this->resolve('Spain and France');

        $this->assertNotNull($result);
        $this->assertCount(0, $result['covers_all']->collection);
    }

    #[Test]
    public function itNeverReturnsCoveringProductsForASingleDestination(): void
    {
        $this->term('Spain')->products()->attach($this->product(0));

        $result = $this->resolve('Spain');

        $this->assertNotNull($result);
        $this->assertCount(0, $result['covers_all']->collection);
    }

    #[Test]
    public function itExcludesProductsWithNoCurrentPrice(): void
    {
        $spain = $this->term('Spain');

        $spain->products()->attach($this->product(0));
        $spain->products()->attach($this->product(1));

        ShopPrice::query()->where('purchasable_id', $this->product(1)->id)->delete();

        $result = $this->resolve('Spain');

        $this->assertNotNull($result);
        $this->assertCount(1, $result['destinations'][0]['products']->collection);
    }

    #[Test]
    public function itIncrementsTheHitCounterForEveryResolvedTerm(): void
    {
        $spain = $this->term('Spain');
        $france = $this->term('France');

        $spain->products()->attach($this->product(0));
        $france->products()->attach($this->product(1));

        $this->resolve('Spain and France');

        $this->assertEquals(1, $spain->refresh()->hits);
        $this->assertEquals(1, $france->refresh()->hits);
    }

    #[Test]
    public function itReturnsTheCountryFlagCodeWhereThereIsOne(): void
    {
        $this->term('Spain')->products()->attach($this->product(0));
        $this->term('Klingon', 'language')->products()->attach($this->product(1));

        $this->assertEquals('es', $this->resolve('Spain')['destinations'][0]['flag']);
        $this->assertNull($this->resolve('Klingon')['destinations'][0]['flag']);
    }

    protected function digitalCard(ShopProduct $product, array $attributes = []): ShopDigitalCard
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)
            ->forProduct($product)
            ->state($attributes)
            ->create();

        return $card;
    }

    protected function productWithVariant(): ShopProduct
    {
        /** @var ShopProduct $product */
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        return $product;
    }

    protected function cardCovering(TravelCardSearchTerm $term, ShopProduct $product, string $language, array $attributes = []): ShopDigitalCard
    {
        $term->products()->attach($product, ['card_language' => $language]);

        return $this->digitalCard($product, array_merge(['language' => $language], $attributes));
    }

    /** @return Collection<int, ShopDigitalCard> */
    protected function digitalCards(string $searchString, ?ShopOrder $basket = null): Collection
    {
        return $this->callAction(ResolveTravelCardSearchAction::class, $searchString, $basket)['destinations'][0]['digital_cards']->collection;
    }

    #[Test]
    public function itReturnsTheDigitalCardsBelongingToTheTermsProducts(): void
    {
        $this->cardCovering($this->term('Spain'), $this->product(0), 'Spanish', ['title' => 'Spanish']);

        $this->digitalCard($this->product(1), ['title' => 'German', 'language' => 'German']);

        $this->assertEquals(['Spanish'], $this->digitalCards('Spain')->pluck('title')->all());
    }

    #[Test]
    public function itOnlyReturnsTheCardForTheLanguageThatCoversTheTerm(): void
    {
        $this->cardCovering($this->term('Spain'), $this->product(0), 'Spanish', ['title' => 'Spanish']);

        $this->digitalCard($this->product(0), ['title' => 'Italian', 'language' => 'Italian']);

        $this->assertEquals(['Spanish'], $this->digitalCards('Spain')->pluck('title')->all());
    }

    #[Test]
    public function itOnlyReturnsLiveDigitalCards(): void
    {
        $spain = $this->term('Spain');

        $this->cardCovering($spain, $this->product(0), 'Spanish', ['title' => 'Spanish']);
        $this->cardCovering($spain, $this->productWithVariant(), 'French', ['title' => 'French', 'live' => false]);

        $this->assertEquals(['Spanish'], $this->digitalCards('Spain')->pluck('title')->all());
    }

    #[Test]
    public function itReturnsTheDigitalCardsOrderedByTitle(): void
    {
        $spain = $this->term('Spain');

        $this->cardCovering($spain, $this->product(0), 'Spanish', ['title' => 'Spanish']);
        $this->cardCovering($spain, $this->productWithVariant(), 'French', ['title' => 'French']);
        $this->cardCovering($spain, $this->productWithVariant(), 'German', ['title' => 'German']);

        $this->assertEquals(['French', 'German', 'Spanish'], $this->digitalCards('Spain')->pluck('title')->all());
    }

    #[Test]
    public function itStillReturnsADigitalCardWhenItsProductHasNoCurrentPrice(): void
    {
        $spain = $this->term('Spain');

        $spain->products()->attach($this->product(0));

        $this->cardCovering($spain, $this->product(1), 'Spanish', ['title' => 'Spanish']);

        ShopPrice::query()->where('purchasable_id', $this->product(1)->id)->delete();

        $result = $this->resolve('Spain');

        $this->assertCount(1, $result['destinations'][0]['products']->collection);
        $this->assertEquals(['Spanish'], $result['destinations'][0]['digital_cards']->collection->pluck('title')->all());
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceWhenACardDoesntHaveOne(): void
    {
        $this->cardCovering($this->term('Spain'), $this->product(0), 'Spanish');

        $this->assertTrue($this->digitalCards('Spain')->first()->price->is(ShopDigitalCard::defaultPrice()));
    }

    #[Test]
    public function itReturnsThePriceAssignedToTheCard(): void
    {
        $card = $this->cardCovering($this->term('Spain'), $this->product(0), 'Spanish');

        $this->build(ShopDigitalCardPrice::class)->forCard($card)->create(['price' => 500]);

        $this->assertSame(500, $this->digitalCards('Spain')->first()->price->price);
    }

    #[Test]
    public function itFlagsWhenTheCardsPhysicalProductIsAlreadyInTheBasket(): void
    {
        $this->cardCovering($this->term('Spain'), $this->product(0), 'Spanish');

        /** @var ShopOrder $basket */
        $basket = $this->create(ShopOrder::class);

        $this->assertFalse($this->digitalCards('Spain', $basket)->first()->physicalProductInBasket);

        $this->build(ShopOrderItem::class)->inOrder($basket)->inProduct($this->product(0))->create();

        $this->assertTrue($this->digitalCards('Spain', $basket->refresh())->first()->physicalProductInBasket);
    }
}
