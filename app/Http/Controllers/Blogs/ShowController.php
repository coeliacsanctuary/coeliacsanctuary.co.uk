<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blogs;

use App\Actions\Comments\GetCommentsForItemAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\Blogs\Blog;
use App\Resources\Blogs\BlogShowResource;
use Inertia\Response;

class ShowController
{
    public function __invoke(Blog $blog, Inertia $inertia, GetCommentsForItemAction $commentsForItemAction): Response
    {
        return $inertia
            ->title($blog->title)
            ->metaDescription($blog->meta_description)
            ->metaTags(explode(',', $blog->meta_tags))
            ->metaImage($blog->social_image)
            ->alternateMetas([
                'article:publisher' => 'https://www.facebook.com/coeliacsanctuary',
                'article:section' => 'Food',
                'article:published_time' => $blog->created_at,
                'article:modified_time' => $blog->updated_at,
                'article:author' => 'Coeliac Sanctuary',
                'article.tags' => $blog->meta_tags,
            ])
            ->schema($blog->schema()->toScript())
            ->breadcrumbs(collect(array_filter([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Blogs', route('blog.index')),
                new BreadcrumbItemData($blog->title),
            ])))
            ->metaFeed(route('blog.feed'))
            ->render('Blog/Show', [
                'blog' => new BlogShowResource($blog),
                'comments' => fn () => $commentsForItemAction->handle($blog),
            ]);
    }
}
