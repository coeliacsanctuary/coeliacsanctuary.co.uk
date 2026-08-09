<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use PHPUnit\Framework\Attributes\Test;
use App\Jobs\EatingOut\ProcessReviewImagesJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\TemporaryFileUpload;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessReviewImagesJobTest extends TestCase
{
    protected EateryReview $review;

    protected TemporaryFileUpload $fileUpload;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
        Storage::fake('review-images');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->review = $this->build(EateryReview::class)
            ->for($this->build(Eatery::class), 'eatery')
            ->create();

        $file = UploadedFile::fake()->image('foo.jpg');
        $uploadedFile = $file->store('/', 'uploads');

        $this->fileUpload = $this->create(TemporaryFileUpload::class, [
            'filename' => $file->name,
            'path' => $uploadedFile,
        ]);
    }

    #[Test]
    public function itPersistTheImageInTheReviewImagesDisk(): void
    {
        $this->assertFalse(Storage::disk('review-images')->exists($this->fileUpload->path));

        (new ProcessReviewImagesJob($this->review, [$this->fileUpload->id]))->handle();

        $this->assertTrue(Storage::disk('review-images')->exists($this->fileUpload->filename));
    }

    #[Test]
    public function itPersistTheImageThumbnailInTheReviewImagesDiskThumbsDirectory(): void
    {
        $this->assertFalse(Storage::disk('review-images')->exists('thumbs/' . $this->fileUpload->path));

        (new ProcessReviewImagesJob($this->review, [$this->fileUpload->id]))->handle();

        $this->assertTrue(Storage::disk('review-images')->exists('thumbs/' . $this->fileUpload->filename));
    }

    #[Test]
    public function itCreatesAReviewImageRow(): void
    {
        $this->assertEmpty($this->review->images);

        (new ProcessReviewImagesJob($this->review, [$this->fileUpload->id]))->handle();

        $this->assertNotEmpty($this->review->refresh()->images);
        $this->assertCount(1, $this->review->images);
    }
}
