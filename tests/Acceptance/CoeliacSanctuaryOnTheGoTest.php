<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReport;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryVenueType;
use App\Models\Popup;
use App\Models\Recipes\Recipe;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoeliacSanctuaryOnTheGoTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itCanGetPlacesToEat(): void
    {
        $this->create(Eatery::class, ['name' => 'My Eatery']);

        $this->get('/api/wheretoeat')
            ->assertOk()
            ->assertJsonFragment(['name' => 'My Eatery']);
    }

    #[Test]
    public function itCanGetPlacesViaSearchTerm(): void
    {
        $this->create(Eatery::class, [
            'name' => 'My Eatery',
            'lat' => 51.5, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
        ]);

        $params = ['term' => 'London', 'range' => 15, 'lat' => 0, 'lng' => 0];

        $this->get('/api/wheretoeat?search=' . json_encode($params))
            ->assertOk()
            ->assertJsonFragment(['name' => 'My Eatery']);
    }

    #[Test]
    public function itCanGetPlacesViaLatLng(): void
    {
        $this->create(Eatery::class, [
            'name' => 'My Eatery',
            'lat' => 51, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
        ]);

        $params = ['term' => '', 'range' => 15, 'lat' => 51, 'lng' => -0.1];

        $this->withoutExceptionHandling();

        $this->get('/api/wheretoeat?search=' . json_encode($params))
            ->assertOk()
            ->assertJsonFragment(['name' => 'My Eatery']);
    }

    #[Test]
    public function itCanGetPlacesWithFilters(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['venue_type' => 'Special Place']);

        $this->create(Eatery::class, [
            'name' => 'My Eatery',
            'lat' => 51, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
            'venue_type_id' => $venueType->id,
        ]);

        $this->create(Eatery::class, [
            'name' => 'My Other Eatery',
            'lat' => 51, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
        ]);

        $params = ['term' => '', 'range' => 15, 'lat' => 51, 'lng' => -0.1];

        $this->get('/api/wheretoeat?search=' . json_encode($params) . '&filter[venueType]=' . $venueType->id)
            ->assertOk()
            ->assertJsonFragment(['name' => 'My Eatery'])
            ->assertJsonMissing(['name' => 'My Other Eatery']);
    }

    #[Test]
    public function itCanGetPaginatedPlaces(): void
    {
        $this->build(Eatery::class)->count(20)->create();

        $this->get('/api/wheretoeat')->assertOk();
        $this->get('/api/wheretoeat?page=2')->assertOk();
    }

    #[Test]
    public function itGetsPlacesAndReturnsTheRequiredData(): void
    {
        $eatery = $this->create(Eatery::class, [
            'name' => 'My Eatery',
            'lat' => 51, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
        ]);

        $params = ['term' => '', 'range' => 15, 'lat' => 51, 'lng' => -0.1];

        $this->build(EateryReview::class)->on($eatery)->approved()->create();

        $this->get('/api/wheretoeat?search=' . json_encode($params))
            ->assertJsonStructure([
                'data' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total',
                    'data' => [
                        [
                            'address',
                            'average_rating',
                            'created_at',
                            'county' => [
                                'county',
                                'id',
                            ],
                            'country' => [
                                'country',
                                'id',
                            ],
                            'cuisine' => [
                                'cuisine',
                                'id',
                            ],
                            'features' => [],
                            'icon',
                            'id',
                            'info',
                            'lat',
                            'lng',
                            'name',
                            'phone',
                            'ratings' => [],
                            'restaurants' => [],
                            'town' => [
                                'id',
                                'town',
                            ],
                            'type' => [
                                'id',
                                'name',
                                'type',
                            ],
                            'venue_type' => [
                                'id',
                                'venue_type',
                            ],
                            'website',
                        ],
                    ],
                    //                    'appends' => [
                    //                        'latlng' => ['lat', 'lng'],
                    //                    ],
                ],
            ]);
    }

    #[Test]
    public function itCanGetPlacesToEatForTheMap(): void
    {
        $eatery = $this->create(Eatery::class, [
            'name' => 'My Eatery',
            'lat' => 51, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
        ]);

        $this->withoutExceptionHandling();

        $this->get('/api/wheretoeat/browse?lat=51&lng=-0.1&range=15')
            ->assertOk()
            ->assertJsonFragment(['id' => $eatery->id]);
    }

    #[Test]
    public function itCanGetPlacesWithFiltersForTheMap(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['venue_type' => 'Special Place']);

        $validEatery = $this->create(Eatery::class, [
            'name' => 'My Eatery',
            'lat' => 51, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
            'venue_type_id' => $venueType->id,
        ]);

        $invalidEatery = $this->create(Eatery::class, [
            'name' => 'My Other Eatery',
            'lat' => 51, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
        ]);

        $this->get('/api/wheretoeat/browse?lat=51&lng=-0.1&range=15&filter[venueType]=' . $venueType->id)
            ->assertOk()
            ->assertJsonFragment(['id' => $validEatery->id])
            ->assertJsonMissing(['id' => $invalidEatery->id]);
    }

    #[Test]
    public function itReturnsTheRequiredDataForTheMap(): void
    {
        $this->create(Eatery::class, [
            'name' => 'My Eatery',
            'lat' => 51, // London
            'lng' => -0.1, // London
            'county_id' => $this->create(EateryCounty::class)->id,
        ]);

        $this->get('/api/wheretoeat/browse?lat=51&lng=-0.1&range=15')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'lat',
                    'lng',
                ]],
            ]);
    }

    #[Test]
    public function itCanGetAPlaceDetails(): void
    {
        $eatery = $this->create(Eatery::class);

        $this
            ->withHeader('User-Agent', 'CoeliacSanctuaryOntheGo')
            ->getJson('/api/wheretoeat/' . $eatery->id)
            ->assertOk()
            ->assertJsonStructure([
                'address',
                'average_rating',
                'created_at',
                'county' => [
                    'county',
                    'id',
                ],
                'country' => [
                    'country',
                    'id',
                ],
                'cuisine' => [
                    'cuisine',
                    'id',
                ],
                'features' => [],
                'icon',
                'id',
                'info',
                'lat',
                'lng',
                'name',
                'phone',
                'ratings' => [],
                'restaurants' => [],
                'town' => [
                    'id',
                    'town',
                ],
                'type' => [
                    'id',
                    'name',
                    'type',
                ],
                'venue_type' => [
                    'id',
                    'venue_type',
                ],
                'website',
            ]);
    }

    #[Test]
    public function itCanGetNationwidePlaces(): void
    {
        $this->create(Eatery::class);

        $this->get('/api/wheretoeat?filter[county]=1')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        [
                            'address',
                            'average_rating',
                            'created_at',
                            'county' => [
                                'county',
                                'id',
                            ],
                            'country' => [
                                'country',
                                'id',
                            ],
                            'cuisine' => [
                                'cuisine',
                                'id',
                            ],
                            'features' => [],
                            'icon',
                            'id',
                            'info',
                            'lat',
                            'lng',
                            'name',
                            'phone',
                            'ratings' => [],
                            'restaurants' => [],
                            'town' => [
                                'id',
                                'town',
                            ],
                            'type' => [
                                'id',
                                'name',
                                'type',
                            ],
                            'venue_type' => [
                                'id',
                                'venue_type',
                            ],
                            'website',
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function itCanGetTheLatestBlogs(): void
    {
        $this->withBlogs(1, function (): void {
            Blog::query()->first()->update(['title' => 'My Blog']);
        });

        $this->get('/api/blogs')->assertOk()->assertJsonFragment(['title' => 'My Blog']);
    }

    #[Test]
    public function itCanGetTheLatestRecipes(): void
    {
        $this->withRecipes(1, function (): void {
            Recipe::query()->first()->update(['title' => 'My Recipe']);
        });

        $this->get('/api/recipes')->assertOk()->assertJsonFragment(['title' => 'My Recipe']);
    }

    #[Test]
    public function itCanGetTheWhereToEatSummary(): void
    {
        $this->get('/api/wheretoeat/summary')
            ->assertOk()
            ->assertJsonStructure(['total', 'eateries', 'attractions', 'hotels', 'reviews']);
    }

    #[Test]
    public function itCanGetTheLatestPlaceRatings(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'My Eatery']);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->count(5)
            ->create();

        $this->get('/api/wheretoeat/ratings/latest')->assertOk();
    }

    #[Test]
    public function itCanGetTheLatestPlacesAdded(): void
    {
        $this->build(Eatery::class)
            ->count(5)
            ->create();

        $this->get('/api/wheretoeat/latest')->assertOk();
    }

    #[Test]
    public function itCanGetTheShopCta(): void
    {
        Storage::fake('media');

        $popup = $this->create(Popup::class);
        $popup->addMedia(UploadedFile::fake()->image('popup.jpg'))->toMediaCollection('primary');

        $this->get('/api/popup')->assertOk();
    }

    #[Test]
    public function itCanGetTheListOfVenueTypes(): void
    {
        $this->get('/api/wheretoeat/venueTypes')->assertOk();
    }

    #[Test]
    public function itCanSearchForALatLng(): void
    {
        $this->post('/api/wheretoeat/lat-lng', ['term' => 'London'])->assertOk();
    }

    #[Test]
    public function itCanSubmitAnEateryRating(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'My Eatery']);

        $this->assertCount(0, EateryReview::all());

        $this->postJson("/api/wheretoeat/{$eatery->id}/reviews", ['rating' => 5, 'method' => 'app'])
            ->assertOk();

        $this->assertCount(1, EateryReview::all());
    }

    #[Test]
    public function itCanSubmitAnEateryReview(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'My Eatery']);

        $this->assertCount(0, EateryReview::all());

        $this->postJson("/api/wheretoeat/{$eatery->id}/reviews", [
            'rating' => 5,
            'name' => 'foo',
            'email' => 'foo@bar.com',
            'comment' => 'foo bar baz',
            'method' => 'app',
        ])->assertOk();

        $this->assertCount(1, EateryReview::withoutGlobalScopes()->get());
    }

    #[Test]
    public function itCanRecommendAPlace(): void
    {
        $this->setUpFaker();

        Mail::fake();

        $this->assertEmpty(EateryRecommendation::all());

        $this->postJson('/api/wheretoeat/recommend-a-place', [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'place_name' => $this->faker->company,
            'place_location' => $this->faker->address,
            'place_web_address' => $this->faker->url,
            'place_details' => $this->faker->paragraph,
        ])->assertOk();

        $this->assertCount(1, EateryRecommendation::all());
    }

    #[Test]
    public function itCanReportAPlace(): void
    {
        Mail::fake();

        $eatery = $this->create(Eatery::class, ['name' => 'My Eatery']);

        $this->assertCount(0, EateryReport::all());

        $this->setUpFaker();

        $this->postJson("/api/wheretoeat/{$eatery->id}/report", [
            'details' => $this->faker->paragraph,
        ])->assertOk();

        $this->assertCount(1, EateryReport::all());
    }

    #[Test]
    public function itCanGetACsrfToken(): void
    {
        $this->get('/api/app-request-token')->assertOk();
    }
}
