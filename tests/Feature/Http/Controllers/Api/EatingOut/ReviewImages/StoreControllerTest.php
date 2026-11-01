<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\ReviewImages;

use PHPUnit\Framework\Attributes\Test;
use App\Models\TemporaryFileUpload;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    #[Test]
    public function itReturnsAValidationErrorIfNoImagesAreSent(): void
    {
        $this->makeRequest()
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('images');
    }

    #[Test]
    public function itErrorIfImagesIsNotAnArray(): void
    {
        $this->makeRequest('foo')
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('images');
    }

    #[Test]
    public function itErrorsIfTheArrayHasMoreThanSixItems(): void
    {
        $this->makeRequest([1, 2, 3, 4, 5, 6, 7])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('images');
    }

    #[Test]
    public function itErrorsIfTheImageItemsArentFiles(): void
    {
        $this->makeRequest(['foo'])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('images.0');
    }

    #[Test]
    public function itErrorsIfTheImagesArentValidImageFiles(): void
    {
        $this->makeRequest([
            UploadedFile::fake()->create('foo.txt', 1, 'text/plain'),
        ])->assertStatus(422)->assertJsonValidationErrorFor('images.0');
    }

    #[Test]
    public function itErrorsIfTheImagesArentAValidMimeType(): void
    {
        $this->makeRequest([UploadedFile::fake()->image('foo.svg')])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('images.0');
    }

    #[Test]
    public function itErrorsIfTheImagesAreGreaterThan5mbInSize(): void
    {
        $this->makeRequest([
            UploadedFile::fake()->create('foo.jpg', 5121),
        ])->assertStatus(422)->assertJsonValidationErrorFor('images.0');
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->makeRequest([UploadedFile::fake()->image('foo.jpg')])->assertOk();
    }

    #[Test]
    public function itStoresTheImageInTheFileUploadsBucket(): void
    {
        Storage::disk('uploads')->assertDirectoryEmpty('/');

        $this->withoutExceptionHandling();

        $this->makeRequest([UploadedFile::fake()->image('foo.jpg')]);

        $this->assertNotEmpty(Storage::disk('uploads')->allFiles('/'));
    }

    #[Test]
    public function itStoresImagesWithARandomName(): void
    {
        $this->makeRequest([UploadedFile::fake()->image('foo.jpg')]);

        Storage::disk('uploads')->assertMissing('foo.jpg');
    }

    #[Test]
    public function itStoresUploadedImagesInTheDatabase(): void
    {
        $this->assertEmpty(TemporaryFileUpload::all());

        $this->makeRequest([UploadedFile::fake()->image('foo.jpg')]);

        $this->assertNotEmpty(TemporaryFileUpload::all());
    }

    #[Test]
    public function itStoresTheCorrectImageUploadProperties(): void
    {
        TestTime::freeze();

        $this->makeRequest([$file = UploadedFile::fake()->image('foo.jpg')]);

        $fileUpload = TemporaryFileUpload::query()->first();

        $this->assertEquals($file->hashName(), $fileUpload->filename);
        $this->assertEquals($file->getSize(), $fileUpload->filesize);
        $this->assertEquals($file->getMimeType(), $fileUpload->mime);
        $this->assertEquals('wte-review-image', $fileUpload->source);
        $this->assertEquals(Carbon::now()->addMinutes(15)->toDateTimeString(), $fileUpload->delete_at);
    }

    #[Test]
    public function itReturnsAnImagesArray(): void
    {
        $this->makeRequest([UploadedFile::fake()->image('foo.jpg')])->assertJsonStructure(['images']);
    }

    #[Test]
    public function itReturnsAObjectForEachImage(): void
    {
        $this->makeRequest([
            UploadedFile::fake()->image('foo.jpg'),
            UploadedFile::fake()->image('bar.jpg'),
        ])->assertJsonCount(2, 'images');
    }

    #[Test]
    public function itReturnsAFormattedObjectForEachImage(): void
    {
        $this->makeRequest([
            UploadedFile::fake()->image('foo.jpg'),
            UploadedFile::fake()->image('bar.jpg'),
        ])->assertJsonStructure([
            'images' => [['id', 'path']],
        ]);
    }

    #[Test]
    public function itReturnsTheIdFromTheDatabase(): void
    {
        $response = $this->makeRequest([UploadedFile::fake()->image('foo.jpg')])->json();

        $fileUpload = TemporaryFileUpload::query()->first();

        $this->assertEquals($fileUpload->id, $response['images'][0]['id']);
    }

    #[Test]
    public function itReturnsAImagePathFromTheDatabase(): void
    {
        TestTime::freeze();

        $response = $this->makeRequest([UploadedFile::fake()->image('foo.jpg')])->json();

        $fileUpload = TemporaryFileUpload::query()->first();

        $this->assertEquals(
            Storage::disk('uploads')->temporaryUrl($fileUpload->path, Carbon::now()->addMinute()),
            $response['images'][0]['path']
        );
    }

    protected function makeRequest($images = null): TestResponse
    {
        return $this->postJson(route('api.wheretoeat.review.image-upload'), [
            'images' => $images,
        ]);
    }
}
