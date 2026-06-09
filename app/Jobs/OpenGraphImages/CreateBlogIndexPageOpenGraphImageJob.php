<?php

declare(strict_types=1);

namespace App\Jobs\OpenGraphImages;

use App\Models\Blogs\Blog;
use App\Models\OpenGraphImage;
use App\Services\RenderOpenGraphImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateBlogIndexPageOpenGraphImageJob implements ShouldQueue
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

        $blogs = Blog::query()->with(['media'])->latest()->take(4)->get();

        $base64Image = $renderOpenGraphImage->handle(view('og-images.blog', [
            'blogs' => $blogs,
        ])->render());

        /** @var OpenGraphImage $openGraphModel */
        $openGraphModel = OpenGraphImage::query()->firstOrCreate(['route' => 'blog']);

        $openGraphModel->clearMediaCollection();
        $openGraphModel->addMediaFromBase64($base64Image)->usingFileName('og-image.png')->toMediaCollection();
        $openGraphModel->touch();
    }
}
