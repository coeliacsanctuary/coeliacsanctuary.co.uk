<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\TravelCards;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Shop\GetTravelCardReviewsAction;
use App\Actions\Shop\TravelCardSearch\GetPopularTravelCardDestinationsAction;
use App\Actions\Shop\TravelCardSearch\GetTravelCardDigitalTypesAction;
use App\Actions\Shop\TravelCardSearch\ResolveTravelCardSearchAction;
use App\Models\Shop\ShopDigitalCardType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        ShopDigitalCardType::query()
            ->get()
            ->each(fn (ShopDigitalCardType $type) => $type
                ->addMedia(UploadedFile::fake()->image('type.jpg'))
                ->toMediaCollection('primary'));
    }

    /** @return array{term: string, destinations: array<int, array{term: string}>, covers_all: array<int, mixed>} */
    protected function resolved(string $term = 'Spain'): array
    {
        return [
            'term' => $term,
            'destinations' => [[
                'term' => $term,
                'type' => 'country',
                'flag' => 'es',
                'products' => [],
                'digital_cards' => [],
            ]],
            'covers_all' => [],
        ];
    }

    #[Test]
    public function itLoadsThePageAndRendersTheInertiaView(): void
    {
        $this->get(route('shop.travel-cards.landing-page'))
            ->assertInertia(fn (Assert $page) => $page->component('Shop/TravelCards'))
            ->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, ['shop']);

        $this->get(route('shop.travel-cards.landing-page'));
    }

    #[Test]
    public function itLoadsTheDestinationsAndReviews(): void
    {
        $this->expectAction(
            GetPopularTravelCardDestinationsAction::class,
            return: collect([['term' => 'Spain', 'flag' => 'es']]),
        );

        $this->expectAction(
            GetTravelCardReviewsAction::class,
            return: ['reviews' => [], 'count' => 5, 'average' => 4.9],
        );

        $this->get(route('shop.travel-cards.landing-page'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('destinations.0.term', 'Spain')
                ->where('reviews.count', 5)
                ->where('reviews.average', 4.9));
    }

    #[Test]
    public function itDoesntResolveASearchWhenNoTermIsGiven(): void
    {
        $this->dontExpectAction(ResolveTravelCardSearchAction::class);

        $this->get(route('shop.travel-cards.landing-page'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('searchTerm', null)
                ->where('search', null));
    }

    #[Test]
    public function itResolvesTheSearchTermFromTheQueryString(): void
    {
        $this->expectAction(ResolveTravelCardSearchAction::class, ['Spain', null], return: $this->resolved());

        $this->get(route('shop.travel-cards.landing-page', ['term' => 'Spain']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('searchTerm', 'Spain')
                ->where('search.term', 'Spain'));
    }

    #[Test]
    public function itTrimsTheSearchTermBeforeResolvingIt(): void
    {
        $this->expectAction(ResolveTravelCardSearchAction::class, ['Spain', null], return: $this->resolved());

        $this->get(route('shop.travel-cards.landing-page', ['term' => '  Spain  ']))
            ->assertInertia(fn (Assert $page) => $page->where('searchTerm', 'Spain'));
    }

    #[Test]
    public function itIgnoresAnEmptySearchTerm(): void
    {
        $this->dontExpectAction(ResolveTravelCardSearchAction::class);

        $this->get(route('shop.travel-cards.landing-page', ['term' => '   ']))
            ->assertInertia(fn (Assert $page) => $page->where('searchTerm', null));
    }

    #[Test]
    public function itKeepsTheSearchTermWhenNothingResolves(): void
    {
        $this->expectAction(ResolveTravelCardSearchAction::class, ['Narnia', null], return: null);

        $this->get(route('shop.travel-cards.landing-page', ['term' => 'Narnia']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('searchTerm', 'Narnia')
                ->where('search', null));
    }

    #[Test]
    public function itNamesThePageAfterTheResolvedDestinations(): void
    {
        $this->expectAction(ResolveTravelCardSearchAction::class, ['Spain', null], return: $this->resolved());

        $this->get(route('shop.travel-cards.landing-page', ['term' => 'Spain']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('meta.title', 'Gluten Free Travel and Translation Cards for Spain'));
    }

    #[Test]
    public function itPassesTheResolvedDigitalCardsToThePage(): void
    {
        $resolved = $this->resolved();
        $resolved['destinations'][0]['digital_cards'] = [['id' => 1, 'title' => 'Spanish Digital Card']];

        $this->expectAction(ResolveTravelCardSearchAction::class, ['Spain', null], return: $resolved);

        $this->get(route('shop.travel-cards.landing-page', ['term' => 'Spain']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('search.destinations.0.digital_cards.0.title', 'Spanish Digital Card'));
    }

    #[Test]
    public function itLoadsTheDigitalCardTypes(): void
    {
        $this->expectAction(
            GetTravelCardDigitalTypesAction::class,
            return: collect([['title' => 'Digital Coeliac Gluten Free Travel Cards']]),
        );

        $this->get(route('shop.travel-cards.landing-page'))
            ->assertInertia(fn (Assert $page) => $page
                ->where(
                    'digitalCardTypes.0.title',
                    'Digital Coeliac Gluten Free Travel Cards',
                ));
    }

    #[Test]
    public function itAddsTheBreadcrumbSchema(): void
    {
        $this->get(route('shop.travel-cards.landing-page'))
            ->assertInertia(fn (Assert $page) => $page->where(
                'meta.schema',
                fn (Collection $schema) => $schema->contains(
                    fn (string $entry) => str_contains($entry, 'BreadcrumbList')
                        && str_contains($entry, 'Gluten Free Travel and Translation Cards'),
                ),
            ));
    }
}
