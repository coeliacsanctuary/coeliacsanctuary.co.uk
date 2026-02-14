<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\OpenGraphImages;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Jobs\OpenGraphImages\CreateBlogIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateCollectionIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryAppPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryMapPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateHomePageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateRecipeIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateShopIndexPageOpenGraphImageJob;
use App\Models\OpenGraphImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GetOpenGraphImageForRouteActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('coeliac.generate_og_images', true);
    }

    #[Test]
    public function itReturnsFromTheModelIfItExistsForTheGivenRoute(): void
    {
        Bus::fake();
        Storage::fake('media');

        $openGraphImage = $this->create(OpenGraphImage::class, [
            'route' => 'test',
        ]);

        $openGraphImage->addMedia(UploadedFile::fake()->image('og-image.jpg'))->toMediaCollection();

        app(GetOpenGraphImageForRouteAction::class)->handle('test');

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function itDispatchesTheCorrectJobIfAnOpenGraphImageDoesntExist(): void
    {
        Bus::fake();
        Storage::fake('media');

        $jobs = [
            'blog' => CreateBlogIndexPageOpenGraphImageJob::class,
            'recipe' => CreateRecipeIndexPageOpenGraphImageJob::class,
            'collection' => CreateCollectionIndexPageOpenGraphImageJob::class,
            'shop' => CreateShopIndexPageOpenGraphImageJob::class,
            'eatery' => CreateEateryIndexPageOpenGraphImageJob::class,
            'eatery-app' => CreateEateryAppPageOpenGraphImageJob::class,
            'eatery-map' => CreateEateryMapPageOpenGraphImageJob::class,
        ];

        foreach ($jobs as $route => $job) {
            app(GetOpenGraphImageForRouteAction::class)->handle($route);

            Bus::assertDispatched($job);
        }
    }

    #[Test]
    public function itDispatchesTheDefaultJobIfTheJobIsUnknown(): void
    {
        Bus::fake();
        Storage::fake('media');

        app(GetOpenGraphImageForRouteAction::class)->handle('foobar');

        Bus::assertDispatched(CreateHomePageOpenGraphImageJob::class);
    }

    #[Test]
    public function itDispatchesTheJobIfTheImageIsOlderThan24Hours(): void
    {
        Bus::fake();
        Storage::fake('media');

        // Create an old open graph image (25 hours ago)
        $openGraphImage = $this->create(OpenGraphImage::class, [
            'route' => 'blog',
            'updated_at' => now()->subHours(25),
        ]);

        $openGraphImage->addMedia(UploadedFile::fake()->image('og-image.jpg'))->toMediaCollection();

        app(GetOpenGraphImageForRouteAction::class)->handle('blog');

        // Should dispatch the job to regenerate the image since it's older than 24 hours
        Bus::assertDispatched(CreateBlogIndexPageOpenGraphImageJob::class);
    }

    #[Test]
    public function itDoesNotDispatchTheJobIfTheImageIsNewerThan24Hours(): void
    {
        Bus::fake();
        Storage::fake('media');

        // Create a recent open graph image (23 hours ago)
        $openGraphImage = $this->create(OpenGraphImage::class, [
            'route' => 'recipe',
            'updated_at' => now()->subHours(23),
        ]);

        $openGraphImage->addMedia(UploadedFile::fake()->image('og-image.jpg'))->toMediaCollection();

        app(GetOpenGraphImageForRouteAction::class)->handle('recipe');

        // Should NOT dispatch the job since the image is still fresh (within 24 hours)
        Bus::assertNothingDispatched();
    }
}
