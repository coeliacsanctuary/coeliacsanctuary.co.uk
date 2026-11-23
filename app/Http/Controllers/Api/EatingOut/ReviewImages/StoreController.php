<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\ReviewImages;

use App\Http\Requests\EatingOut\Api\ReviewImagesUploadRequest;
use App\Pipelines\Shared\UploadTemporaryFile\UploadTemporaryFilePipeline;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class StoreController
{
    public function __invoke(ReviewImagesUploadRequest $request, UploadTemporaryFilePipeline $pipeline): array
    {
        /** @var array<UploadedFile> $images */
        $images = $request->file('images');

        return [
            'images' => collect($images)
                ->map(fn (UploadedFile $file): array => $pipeline->run(
                    $file,
                    'wte-review-image',
                    Carbon::now()->addMinutes(15),
                )),
        ];
    }
}
