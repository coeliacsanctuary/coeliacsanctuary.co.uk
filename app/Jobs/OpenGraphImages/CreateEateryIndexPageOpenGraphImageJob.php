<?php

declare(strict_types=1);

namespace App\Jobs\OpenGraphImages;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Models\OpenGraphImage;
use App\Services\RenderOpenGraphImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateEateryIndexPageOpenGraphImageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function handle(RenderOpenGraphImage $renderOpenGraphImage): void
    {
        if (config('coeliac.generate_og_images') === false) {
            return;
        }

        $eateries = Eatery::query()
            ->where('type_id', EateryType::EATERY)
            ->count();

        $attractions = Eatery::query()
            ->where('type_id', EateryType::ATTRACTION)
            ->count();

        $hotels = Eatery::query()
            ->where('type_id', EateryType::HOTEL)
            ->count();

        $branches = NationwideBranch::query()->count();

        $reviews = EateryReview::query()->count();

        $base64Image = $renderOpenGraphImage->handle(view('og-images.eatery', [
            'eateries' => $eateries + $branches,
            'attractions' => $attractions,
            'hotels' => $hotels,
            'branches' => $branches,
            'reviews' => $reviews,
        ])->render());

        /** @var OpenGraphImage $openGraphModel */
        $openGraphModel = OpenGraphImage::query()->firstOrCreate(['route' => 'eatery']);

        $openGraphModel->clearMediaCollection();
        $openGraphModel->addMediaFromBase64($base64Image)->usingFileName('og-image.png')->toMediaCollection();
        $openGraphModel->touch();
    }
}
