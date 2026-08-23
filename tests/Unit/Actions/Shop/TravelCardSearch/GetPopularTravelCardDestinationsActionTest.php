<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\GetPopularTravelCardDestinationsAction;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetPopularTravelCardDestinationsActionTest extends TestCase
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

    protected function term(string $term, int $hits, string $type = 'country', bool $withProduct = true): TravelCardSearchTerm
    {
        /** @var TravelCardSearchTerm $searchTerm */
        $searchTerm = $this->create(TravelCardSearchTerm::class, [
            'term' => $term,
            'type' => $type,
            'hits' => $hits,
        ]);

        if ($withProduct) {
            $searchTerm->products()->attach($this->product(0));
        }

        return $searchTerm;
    }

    /** @return Collection<int, array{term: string, flag: string|null}> */
    protected function destinations(): Collection
    {
        return $this->callAction(GetPopularTravelCardDestinationsAction::class);
    }

    #[Test]
    public function itReturnsTheMostSearchedDestinationsFirst(): void
    {
        $this->term('france', 200);
        $this->term('spain', 500);
        $this->term('italy', 300);

        $this->assertEquals(['Spain', 'Italy', 'France'], $this->destinations()->pluck('term')->all());
    }

    #[Test]
    public function itReturnsTheFlagCodeAlongsideTheTerm(): void
    {
        $this->term('spain', 500);

        $this->assertEquals(['term' => 'Spain', 'flag' => 'es'], $this->destinations()->first());
    }

    #[Test]
    public function itOnlyReturnsCountries(): void
    {
        $this->term('spanish', 900, 'language');
        $this->term('spain', 100);

        $this->assertEquals(['Spain'], $this->destinations()->pluck('term')->all());
    }

    #[Test]
    public function itIgnoresTermsWithNoProducts(): void
    {
        $this->term('narnia', 900, 'country', withProduct: false);
        $this->term('spain', 100);

        $this->assertEquals(['Spain'], $this->destinations()->pluck('term')->all());
    }

    #[Test]
    public function itReturnsAtMostTenDestinations(): void
    {
        foreach (range(1, 14) as $index) {
            $this->term("country {$index}", $index);
        }

        $this->assertCount(10, $this->destinations());
    }

    #[Test]
    public function itCachesTheDestinations(): void
    {
        $this->term('spain', 500);

        $this->assertFalse(Cache::has('travel-card-popular-destinations'));

        $destinations = $this->destinations();

        $this->assertTrue(Cache::has('travel-card-popular-destinations'));
        $this->assertEquals($destinations, Cache::get('travel-card-popular-destinations'));
    }
}
