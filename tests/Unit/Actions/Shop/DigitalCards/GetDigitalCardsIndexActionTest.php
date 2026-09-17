<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\DigitalCards;

use App\Actions\Shop\DigitalCards\GetDigitalCardsIndexAction;
use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetDigitalCardsIndexActionTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ShopProduct $product */
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        $this->product = $product;
    }

    protected function card(array $attributes = []): ShopDigitalCard
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)
            ->forProduct($this->product)
            ->state($attributes)
            ->create();

        return $card;
    }

    protected function attachTerm(string $term, string $type = 'country', string $language = 'french', int $score = 10, bool $showOnProductPage = true): TravelCardSearchTerm
    {
        /** @var TravelCardSearchTerm $searchTerm */
        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => $type]);

        $this->product->travelCardSearchTerms()->attach($searchTerm, [
            'card_language' => $language,
            'card_score' => $score,
            'card_show_on_product_page' => $showOnProductPage,
        ]);

        return $searchTerm;
    }

    /** @return Collection<int, ShopDigitalCard> */
    protected function cards(DigitalCardType $type = DigitalCardType::STANDARD): Collection
    {
        return $this->callAction(GetDigitalCardsIndexAction::class, $type);
    }

    #[Test]
    public function itOnlyReturnsCardsOfTheGivenType(): void
    {
        $this->card(['title' => 'French', 'type' => DigitalCardType::STANDARD]);
        $this->card(['title' => 'German', 'type' => DigitalCardType::PLUS]);

        $this->assertEquals(['French'], $this->cards()->pluck('title')->all());
        $this->assertEquals(['German'], $this->cards(DigitalCardType::PLUS)->pluck('title')->all());
    }

    #[Test]
    public function itOnlyReturnsLiveCards(): void
    {
        $this->card(['title' => 'French']);
        $this->card(['title' => 'German', 'live' => false]);

        $this->assertEquals(['French'], $this->cards()->pluck('title')->all());
    }

    #[Test]
    public function itReturnsTheCardsOrderedByTitle(): void
    {
        $this->card(['title' => 'Spanish']);
        $this->card(['title' => 'French']);
        $this->card(['title' => 'German']);

        $this->assertEquals(['French', 'German', 'Spanish'], $this->cards()->pluck('title')->all());
    }

    #[Test]
    public function itEagerLoadsTheProductAndItsRelations(): void
    {
        $this->card();

        $card = $this->cards()->first();

        $this->assertTrue($card->relationLoaded('product'));
        $this->assertTrue($card->product->relationLoaded('variants'));
        $this->assertTrue($card->product->relationLoaded('travelCardSearchTerms'));
    }

    #[Test]
    public function itReturnsThePriceAssignedToTheCard(): void
    {
        $card = $this->card();

        $this->build(ShopDigitalCardPrice::class)->forCard($card)->create(['price' => 500]);

        $this->assertSame(500, $this->cards()->first()->price->price);
    }

    #[Test]
    public function itFallsBackToTheDefaultPriceWhenTheCardDoesntHaveOne(): void
    {
        $this->card();

        $this->assertTrue($this->cards()->first()->price->is(ShopDigitalCard::defaultPrice()));
    }

    #[Test]
    public function itAssignsTheProductsCountriesToTheCard(): void
    {
        $this->card(['language' => 'french']);

        $this->attachTerm('france');

        $this->assertEquals(['france'], $this->cards()->first()->countries->pluck('term')->all());
    }

    #[Test]
    public function itDoesntAssignAnyCountriesWhenTheCardDoesntHaveAProduct(): void
    {
        $this->build(ShopDigitalCard::class)->create();

        $this->assertFalse($this->cards()->first()->relationLoaded('countries'));
    }

    #[Test]
    public function itDoesntAssignAnyCountriesWhenTheProductDoesntHaveAnySearchTerms(): void
    {
        $this->card(['language' => 'french']);

        $this->assertFalse($this->cards()->first()->relationLoaded('countries'));
    }

    #[Test]
    public function itDoesntAssignTermsThatArentCountries(): void
    {
        $this->card(['language' => 'french']);

        $this->attachTerm('france');
        $this->attachTerm('french', type: 'language');

        $this->assertEquals(['france'], $this->cards()->first()->countries->pluck('term')->all());
    }

    #[Test]
    public function itDoesntAssignTermsForAnotherLanguage(): void
    {
        $this->card(['language' => 'french']);

        $this->attachTerm('france');
        $this->attachTerm('germany', language: 'german');

        $this->assertEquals(['france'], $this->cards()->first()->countries->pluck('term')->all());
    }

    #[Test]
    public function itDoesntAssignTermsThatArentFlaggedToShowOnTheProductPage(): void
    {
        $this->card(['language' => 'french']);

        $this->attachTerm('france');
        $this->attachTerm('belgium', showOnProductPage: false);

        $this->assertEquals(['france'], $this->cards()->first()->countries->pluck('term')->all());
    }

    #[Test]
    public function itAssignsTheCountriesWithTheHighestScoreFirst(): void
    {
        $this->card(['language' => 'french']);

        $this->attachTerm('belgium', score: 20);
        $this->attachTerm('france', score: 100);
        $this->attachTerm('switzerland', score: 50);

        $this->assertEquals(['france', 'switzerland', 'belgium'], $this->cards()->first()->countries->pluck('term')->all());
    }
}
