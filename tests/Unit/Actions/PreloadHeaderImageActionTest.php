<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\PreloadHeaderImageAction;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\Recipes\Recipe;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\SeedsWebsite;
use Tests\TestCase;

class PreloadHeaderImageActionTest extends TestCase
{
    use SeedsWebsite;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');
    }

    #[Test]
    public function itLoadsTheHomeHeroImageIfOnTheHomePage(): void
    {
        $this->createMockRequest(route('home'));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, '/images/travel-cards-hero.webp');
    }

    #[Test]
    public function itLoadsTheBlogHeaderImageIfOnTheBlogShowPage(): void
    {
        $this->withBlogs(1);

        $blog = Blog::query()->first();

        $this->createMockRequest(route('blog.show', $blog));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $blog->main_image);
    }

    #[Test]
    public function itLoadsTheRecipeHeaderImageIfOnTheRecipeShowPage(): void
    {
        $this->withRecipes(1);

        $recipe = Recipe::query()->first();

        $this->createMockRequest(route('recipe.show', $recipe));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $recipe->main_image);
    }

    #[Test]
    public function itLoadsTheCollectionHeaderImageIfOnTheCollectionShowPage(): void
    {
        $this->withCollections(1);

        $collection = Collection::query()->first();

        $this->createMockRequest(route('collection.show', $collection));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $collection->main_image);
    }

    #[Test]
    public function itLoadsTheCountyImageIfOnTheCountyShowPageAndTheCountyHasAnImage(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $county = EateryCounty::query()->first();

        $county->addMedia(UploadedFile::fake()->image('county.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.county', $county));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $county->image);
    }

    #[Test]
    public function itFallsBackToTheCountryImageIfOnTheCountyShowPageButTheCountyDoesntHaveAnImage(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $county = EateryCounty::query()->first();

        $this->createMockRequest(route('eating-out.county', $county));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $county->country->image);
    }

    #[Test]
    public function itLoadsTheLondonImageIfOnTheLondonPage(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $county->update(['slug' => 'london']);

        $county->addMedia(UploadedFile::fake()->image('county.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.london'));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $county->image);
    }

    #[Test]
    public function itLoadsTheTownImageIfOnTheTownShowPageAndTheTownHasAnImage(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $town = EateryTown::query()->first();

        $town->addMedia(UploadedFile::fake()->image('town.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.town', ['county' => $county, 'town' => $town]));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $town->image);
    }

    #[Test]
    public function itFallsBackToTheCountyImageIfOnTheTownShowPageAndTheTownDoesntHaveAnImageButTheCountyDoes(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $town = EateryTown::query()->first();

        $county->addMedia(UploadedFile::fake()->image('county.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.town', ['county' => $county, 'town' => $town]));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $county->image);
    }

    #[Test]
    public function itFallsBackToTheCountryImageIfOnTheTownShowPageAndBothTheTownAndCountyDontHaveAnImages(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $town = EateryTown::query()->first();

        $this->createMockRequest(route('eating-out.town', ['county' => $county, 'town' => $town]));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $county->country->image);
    }

    #[Test]
    public function itLoadsTheBoroughImageIfOnTheLondonBoroughShowPageAndTheBoroughHasAnImage(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $county->update(['slug' => 'london']);

        $borough = EateryTown::query()->first();

        $borough->addMedia(UploadedFile::fake()->image('borough.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.london.borough', $borough));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $borough->image);
    }

    #[Test]
    public function itFallsBackToTheLondonCountyImageIfOnTheLondonBoroughShowPageAndTheBoroughDoesntHaveAnImage(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $county->update(['slug' => 'london']);

        $borough = EateryTown::query()->first();

        $county->addMedia(UploadedFile::fake()->image('county.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.london.borough', $borough));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $county->image);
    }

    #[Test]
    public function itLoadsTheLondonAreaImageIfOnTheLondonAreaShowPageAndTheAreaHasAnImage(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $county->update(['slug' => 'london']);

        $borough = EateryTown::query()->first();

        $area = $this->create(EateryArea::class);

        $eatery->update(['area_id' => $area->id]);

        $area->addMedia(UploadedFile::fake()->image('area.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.london.borough.area', ['borough' => $borough, 'area' => $area]));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $area->image);
    }

    #[Test]
    public function itFallsBackToTheBoroughImageIfOnTheLondonAreaShowPageAndTheAreaDoesntHaveAnImage(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $county->update(['slug' => 'london']);

        $borough = EateryTown::query()->first();

        $area = $this->create(EateryArea::class);

        $eatery->update(['area_id' => $area->id]);

        $borough->addMedia(UploadedFile::fake()->image('borough.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.london.borough.area', ['borough' => $borough, 'area' => $area]));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $borough->image);
    }

    #[Test]
    public function itFallsBackToTheLondonImageIfOnTheLondonAreaShowPageAndBothTheAreaAndBoroughDontHaveImages(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $county = EateryCounty::query()->first();
        $county->update(['slug' => 'london']);

        $borough = EateryTown::query()->first();

        $area = $this->create(EateryArea::class);

        $eatery->update(['area_id' => $area->id]);

        $county->addMedia(UploadedFile::fake()->image('county.jpg'))->toMediaCollection('primary');

        $this->createMockRequest(route('eating-out.london.borough.area', ['borough' => $borough, 'area' => $area]));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertPreloadElementContainsImage($preloadElement, $county->image);
    }

    #[Test]
    public function itDefaultsToNullIfThereIsNoImageToPreload(): void
    {
        $this->createMockRequest(route('blog.index'));

        $preloadElement = app(PreloadHeaderImageAction::class)->handle();

        $this->assertNull($preloadElement);
    }

    protected function assertPreloadElementContainsImage(string $preloadElement, string $image): void
    {
        $this->assertEquals(
            $preloadElement,
            "<link rel=\"preload\" as=\"image\" href=\"{$image}\" fetchpriority=\"high\" />",
            "Failed asserting that the preload element contains the image: {$image}",
        );
    }

    protected function createMockRequest(string $url): self
    {
        $request = Request::create($url);

        $this->app->bind(Request::class, fn () => $request);

        app(Router::class)->dispatch($request);

        return $this;
    }
}
