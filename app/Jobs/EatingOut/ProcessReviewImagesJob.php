<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Models\EatingOut\EateryReview;
use App\Models\TemporaryFileUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProcessReviewImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected EateryReview $review, protected array $fileIds)
    {
        //
    }

    public function handle(): void
    {
        TemporaryFileUpload::query()
            ->findMany($this->fileIds)
            ->each(function (TemporaryFileUpload $file): void {
                /** @var string $rawFile */
                $rawFile = Storage::disk('uploads')->get($file->path);

                $this->persistImage($file, $rawFile);
                $this->generateThumbnail($rawFile, $file);
                $this->storeImageRow($file);
            });
    }

    protected function persistImage(TemporaryFileUpload $file, string $rawFile): void
    {
        $image = Image::fromBytes($rawFile)->orient();

        Storage::disk('review-images')->put($file->filename, $image->toBytes(), 'public');
    }

    protected function generateThumbnail(string $rawFile, TemporaryFileUpload $file): void
    {
        $thumbnail = Image::fromBytes($rawFile)
            ->orient()
            ->scale(250, 250)
            ->quality(80);

        Storage::disk('review-images')->put('thumbs/' . $file->filename, $thumbnail->toBytes(), 'public');
    }

    protected function storeImageRow(TemporaryFileUpload $file): void
    {
        $this->review->images()->create([
            'wheretoeat_id' => $this->review->wheretoeat_id,
            'thumb' => 'thumbs/' . $file->filename,
            'path' => $file->path,
        ]);
    }
}
