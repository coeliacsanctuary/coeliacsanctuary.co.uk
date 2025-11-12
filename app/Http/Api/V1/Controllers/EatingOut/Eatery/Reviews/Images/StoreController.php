<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Images;

use App\Http\Api\V1\Requests\EatingOut\ReviewImagesUploadRequest;
use App\Pipelines\Shared\UploadTemporaryFile\UploadTemporaryFilePipeline;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class StoreController
{
    public function __invoke(ReviewImagesUploadRequest $request, UploadTemporaryFilePipeline $pipeline): array
    {
        /** @var array<UploadedFile> $images */
        $images = $request->file('images');

        $images = collect($images)->map(fn (UploadedFile $file) => $pipeline->run(
            $file,
            'wte-review-image',
            Carbon::now()->addMinutes(15),
        ));

        return [
            'data' => $images->map(fn (array $image) => [
                'id' => $image['id'],
                'url' => $image['path'],
            ]),
        ];
    }
}
