<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\OpenGraphImages;

use App\Models\EatingOut\EateryArea;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Contracts\HasOpenGraphImageContract;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GetEatingOutOpenGraphImageActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('coeliac.generate_og_images', true);
    }

    #[Test]
    #[DataProvider('openGraphModelsDataProvider')]
    public function itReturnsFromTheModelIfItExists(string $modelClass): void
    {
        Bus::fake();
        Storage::fake('media');

        /** @var HasOpenGraphImageContract $model */
        $model = $this->build($modelClass)->createQuietly();

        $openGraphImage = $model->openGraphImage()->create();

        $openGraphImage->addMedia(UploadedFile::fake()->image('og-image.jpg'))->toMediaCollection();

        app(GetEatingOutOpenGraphImageAction::class)->handle($model);

        Bus::assertNothingDispatched();
    }

    #[Test]
    #[DataProvider('openGraphModelsDataProvider')]
    public function itDispatchesTheJobIfAnOpenGraphImageDoesntExist(string $modelClass): void
    {
        Bus::fake();
        Storage::fake('media');

        /** @var HasOpenGraphImageContract $model */
        $model = $this->build($modelClass)->createQuietly();

        $this->assertNull($model->openGraphImage);

        app(GetEatingOutOpenGraphImageAction::class)->handle($model);

        Bus::assertDispatched(CreateEatingOutOpenGraphImageJob::class);
    }

    public static function openGraphModelsDataProvider(): array
    {
        return [
            'county' => [EateryCounty::class],
            'town' => [EateryTown::class],
            'area' => [EateryArea::class],
            'eatery' => [Eatery::class],
            'nationwide branch' => [NationwideBranch::class],
        ];
    }
}
