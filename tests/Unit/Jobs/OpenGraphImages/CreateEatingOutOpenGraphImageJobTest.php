<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\OpenGraphImages;

use App\Actions\OpenGraphImages\GenerateAreaOpenGraphImageAction;
use App\Models\EatingOut\EateryArea;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GenerateCountyOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateEateryOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateNationwideBranchOpenGraphImageAction;
use App\Actions\OpenGraphImages\GenerateTownOpenGraphImageAction;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Models\OpenGraphImage;
use App\Services\RenderOpenGraphImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateEatingOutOpenGraphImageJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        config()->set('coeliac.generate_og_images', true);
    }

    #[Test]
    #[DataProvider('openGraphModelsDataProvider')]
    public function itCallsTheRenderOpenGraphImageService($classString, $expectedAction): void
    {
        $this->mock(RenderOpenGraphImage::class)
            ->shouldReceive('handle')
            ->andReturn('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAb0lEQVR4nGK567aCAQZOXAuEs/cf3QZnO3xaDGczMZAIaK+BJVjTFs5RLAyBs1UuS8DZaj8m0tFJJGtgzORshHMELtfA2TMC58LZ7i3MdHQS6fHw6VkPnKMWcRHOlq/JgbOPFO+ko5NI1gAIAAD//3qSF5xOx6hcAAAAAElFTkSuQmCC')
            ->once();

        $model = $this->build($classString)->createQuietly($classString === NationwideBranch::class ? ['wheretoeat_id' => $this->build(Eatery::class)->createQuietly()->id] : []);

        $this->expectAction($expectedAction, return: view('app', ['page' => []]));

        (new CreateEatingOutOpenGraphImageJob($model))->handle(app(RenderOpenGraphImage::class));
    }

    #[Test]
    #[DataProvider('openGraphModelsDataProvider')]
    public function itCreatesAnOpenGraphRecord($classString, $action, $useMedia): void
    {
        $this->assertDatabaseEmpty(OpenGraphImage::class);
        $this->assertEmpty(Storage::disk('media')->allFiles());

        $this->mock(RenderOpenGraphImage::class)
            ->shouldReceive('handle')
            ->andReturn('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAb0lEQVR4nGK567aCAQZOXAuEs/cf3QZnO3xaDGczMZAIaK+BJVjTFs5RLAyBs1UuS8DZaj8m0tFJJGtgzORshHMELtfA2TMC58LZ7i3MdHQS6fHw6VkPnKMWcRHOlq/JgbOPFO+ko5NI1gAIAAD//3qSF5xOx6hcAAAAAElFTkSuQmCC')
            ->once();

        $model = $this->build($classString)->createQuietly($classString === NationwideBranch::class ? ['wheretoeat_id' => $this->build(Eatery::class)->createQuietly()->id] : []);

        if ($useMedia) {
            $model->addMedia(UploadedFile::fake()->image('image.jpg'))->toMediaCollection('primary');
        }

        (new CreateEatingOutOpenGraphImageJob($model))->handle(app(RenderOpenGraphImage::class));

        $this->assertDatabaseCount(OpenGraphImage::class, 1);
        $this->assertNotNull($model->refresh()->openGraphImage);

        Storage::disk('media')->assertExists("opengraphimages/{$model->openGraphImage->id}/og-image.png");
    }

    public static function openGraphModelsDataProvider(): array
    {
        return [
            'county' => [EateryCounty::class, GenerateCountyOpenGraphImageAction::class, true],
            'town' => [EateryTown::class, GenerateTownOpenGraphImageAction::class, true],
            'area' => [EateryArea::class, GenerateAreaOpenGraphImageAction::class, true],
            'eatery' => [Eatery::class, GenerateEateryOpenGraphImageAction::class, false],
            'nationwide branch' => [NationwideBranch::class, GenerateNationwideBranchOpenGraphImageAction::class, false],
        ];
    }
}
