<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\OpenGraphImages;

use App\Jobs\OpenGraphImages\CreateEateryIndexPageOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\OpenGraphImage;
use App\Services\RenderOpenGraphImage;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEateryIndexPageOpenGraphImageJobTest extends TestCase
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

        (new CreateEateryIndexPageOpenGraphImageJob())->handle(app(RenderOpenGraphImage::class));
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

        (new CreateEateryIndexPageOpenGraphImageJob())->handle(app(RenderOpenGraphImage::class));

        $this->assertDatabaseCount(OpenGraphImage::class, 1);
        $model = OpenGraphImage::query()->first();

        Storage::disk('media')->assertExists("opengraphimages/{$model->id}/og-image.png");
    }

    #[Test]
    public function itPassesCorrectGeographicCountsToTheView(): void
    {
        $this->create(Eatery::class, 7, ['country_id' => 1], true);  // England eateries
        $this->create(Eatery::class, 3, ['country_id' => 8], true);  // Wales eateries
        $this->create(Eatery::class, 2, ['country_id' => 7], true);  // Scotland eateries
        $this->create(NationwideBranch::class, 4, ['country_id' => 1], true); // England branches
        $this->create(NationwideBranch::class, 5, ['country_id' => 5], true); // NI branches
        $this->create(NationwideBranch::class, 6, ['country_id' => 6], true); // ROI branches

        // England = 7+4 = 11, Wales = 3, Scotland = 2, NI = 5, ROI = 6, total = 27

        $captured = null;

        $this->mock(RenderOpenGraphImage::class)
            ->shouldReceive('handle')
            ->withArgs(function (string $html) use (&$captured) {
                $captured = $html;

                return true;
            })
            ->andReturn('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAb0lEQVR4nGK567aCAQZOXAuEs/cf3QZnO3xaDGczMZAIaK+BJVjTFs5RLAyBs1UuS8DZaj8m0tFJJGtgzORshHMELtfA2TMC58LZ7i3MdHQS6fHw6VkPnKMWcRHOlq/JgbOPFO+ko5NI1gAIAAD//3qSF5xOx6hcAAAAAElFTkSuQmCC')
            ->once();

        (new CreateEateryIndexPageOpenGraphImageJob())->handle(app(RenderOpenGraphImage::class));

        $this->assertNotNull($captured);
        $this->assertMatchesRegularExpression('/>\s*27\s*</', $captured);  // total
        $this->assertMatchesRegularExpression('/>\s*11\s*</', $captured);  // England
        $this->assertMatchesRegularExpression('/>\s*3\s*</', $captured);   // Wales
        $this->assertMatchesRegularExpression('/>\s*2\s*</', $captured);   // Scotland
        $this->assertMatchesRegularExpression('/>\s*5\s*</', $captured);   // NI
        $this->assertMatchesRegularExpression('/>\s*6\s*</', $captured);   // ROI
    }
}
