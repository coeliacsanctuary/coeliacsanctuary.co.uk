<?php

declare(strict_types=1);

namespace App\Jobs\OpenGraphImages;

use App\Models\EatingOut\Eatery;
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

        $eateries = Eatery::query()->select(['id', 'country_id'])->get();
        $branches = NationwideBranch::query()->select(['id', 'country_id'])->get();

        $wales = $eateries->where('country_id', 8)->count() + $branches->where('country_id', 8)->count();
        $scotland = $eateries->where('country_id', 7)->count() + $branches->where('country_id', 7)->count();
        $roi = $eateries->where('country_id', 6)->count() + $branches->where('country_id', 6)->count();
        $ni = $eateries->where('country_id', 5)->count() + $branches->where('country_id', 5)->count();
        $england = $eateries->whereNotIn('country_id', [5,6,7,8])->count() + $branches->whereNotIn('country_id', [5,6,7,8])->count();

        $base64Image = $renderOpenGraphImage->handle(view('og-images.eatery', [
            'eateries' => $eateries->count() + $branches->count(),
            'wales' => $wales,
            'scotland' => $scotland,
            'roi' => $roi,
            'ni' => $ni,
            'england' => $england,
        ])->render());

        /** @var OpenGraphImage $openGraphModel */
        $openGraphModel = OpenGraphImage::query()->firstOrCreate(['route' => 'eatery']);

        $openGraphModel->clearMediaCollection();
        $openGraphModel->addMediaFromBase64($base64Image)->usingFileName('og-image.png')->toMediaCollection();
        $openGraphModel->touch();
    }
}
