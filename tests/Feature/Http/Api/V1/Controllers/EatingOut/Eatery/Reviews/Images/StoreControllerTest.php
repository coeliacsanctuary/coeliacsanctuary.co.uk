<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Images;

use App\Models\EatingOut\Eatery;
use App\Pipelines\Shared\UploadTemporaryFile\UploadTemporaryFilePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->postJson(route('api.v1.eating-out.details.reviews.images.store', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithNoImages(): void
    {
        $this->makeRequest()->assertJsonValidationErrorFor('images');
    }

    #[Test]
    public function itErrorsWithTooManyImages(): void
    {
        $this->makeRequest([1, 2, 3, 4, 5, 6, 7])->assertJsonValidationErrorFor('images');
    }

    #[Test]
    public function itErrorsIfAnImageIsNotAnImage(): void
    {
        $file = UploadedFile::fake()->create('foo.txt');

        $this->makeRequest([$file])->assertJsonValidationErrorFor('images.0');
    }

    #[Test]
    public function itErrorsIfAnImageIsTooLarge(): void
    {
        $image = UploadedFile::fake()->create('foo.jpg', 10000);

        $this->makeRequest([$image])->assertJsonValidationErrorFor('images.0');
    }

    #[Test]
    public function itCallsTheUploadImagePipelineForEachImage(): void
    {
        $images = [
            UploadedFile::fake()->image('foo.jpg'),
            UploadedFile::fake()->image('bar.jpg'),
        ];

        $this->mock(UploadTemporaryFilePipeline::class)
            ->shouldReceive('run')
            ->twice()
            ->withArgs(function ($file) use ($images) {
                $this->assertContains($file, $images);

                return true;
            })
            ->andReturn(['id' => 123, 'path' => 'foobar']);

        $this->makeRequest($images)->assertOk();
    }

    #[Test]
    public function itReturnsADataKeyAndPayload(): void
    {
        $this->mock(UploadTemporaryFilePipeline::class)
            ->shouldReceive('run')
            ->andReturn(['id' => 123, 'path' => 'foobar']);

        $this->makeRequest([UploadedFile::fake()->image('foo.jpg')])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    ['id' => 123, 'url' => 'foobar'],
                ],
            ]);
    }

    protected function makeRequest(array $images = [], string $source = 'foo'): TestResponse
    {
        return $this->postJson(
            route('api.v1.eating-out.details.reviews.images.store', ['eatery' => $this->eatery]),
            ['images' => $images],
            ['x-coeliac-source' => $source],
        );
    }
}
