<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\OpenGraphImages;

use PHPUnit\Framework\Attributes\Test;
use App\Jobs\OpenGraphImages\CreateEateryCollectionIndexPageOpenGraphImageJob;
use App\Models\OpenGraphImage;
use App\Services\RenderOpenGraphImage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateEateryCollectionIndexPageOpenGraphImageJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        config()->set('coeliac.generate_og_images', true);
    }

    #[Test]
    public function itCallsRenderOpenGraphImageAction(): void
    {
        $this->mock(RenderOpenGraphImage::class)
            ->shouldReceive('handle')
            ->andReturn('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAb0lEQVR4nGK567aCAQZOXAuEs/cf3QZnO3xaDGczMZAIaK+BJVjTFs5RLAyBs1UuS8DZaj8m0tFJJGtgzORshHMELtfA2TMC58LZ7i3MdHQS6fHw6VkPnKMWcRHOlq/JgbOPFO+ko5NI1gAIAAD//3qSF5xOx6hcAAAAAElFTkSuQmCC')
            ->once();

        (new CreateEateryCollectionIndexPageOpenGraphImageJob())->handle(app(RenderOpenGraphImage::class));
    }

    #[Test]
    public function itCreatesAnOpenGraphRecord(): void
    {
        $this->assertDatabaseEmpty(OpenGraphImage::class);
        $this->assertEmpty(Storage::disk('media')->allFiles());

        $this->mock(RenderOpenGraphImage::class)
            ->shouldReceive('handle')
            ->andReturn('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAb0lEQVR4nGK567aCAQZOXAuEs/cf3QZnO3xaDGczMZAIaK+BJVjTFs5RLAyBs1UuS8DZaj8m0tFJJGtgzORshHMELtfA2TMC58LZ7i3MdHQS6fHw6VkPnKMWcRHOlq/JgbOPFO+ko5NI1gAIAAD//3qSF5xOx6hcAAAAAElFTkSuQmCC')
            ->once();

        (new CreateEateryCollectionIndexPageOpenGraphImageJob())->handle(app(RenderOpenGraphImage::class));

        $this->assertDatabaseCount(OpenGraphImage::class, 1);
        $model = OpenGraphImage::query()->first();

        Storage::disk('media')->assertExists("opengraphimages/{$model->id}/og-image.png");
    }
}
