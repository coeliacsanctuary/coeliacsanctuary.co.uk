<?php

declare(strict_types=1);

namespace App\Actions\Blogs;

use App\Models\Blogs\Blog;
use App\Resources\Blogs\BlogSimpleCardViewResource;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetTopBlogsForHomepageAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var AnonymousResourceCollection $blogs */
        $blogs = Cache::flexible(
            'top-blogs',
            [CarbonInterval::minutes(60), CarbonInterval::minutes(5)],
            fn () => BlogSimpleCardViewResource::collection(Blog::query()
                ->withSum(
                    /** @phpstan-ignore-next-line  */
                    ['metrics' => fn (Builder $query) => $query->where('date', '>=', Carbon::now()->subDay()->startOfDay())],
                    'page_views',
                )
                ->take(3)
                ->orderBy('metrics_sum_page_views', 'desc')
                ->with(['media'])
                ->get())
        );

        return $blogs;
    }
}
