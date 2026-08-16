<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blogs;

use App\Actions\Blogs\FindRelatedBlogsAction;
use App\Actions\Comments\GetCommentsForItemAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\Blogs\Blog;
use App\Resources\Blogs\BlogShowResource;
use App\Resources\Blogs\RelatedBlogSimpleCardViewResource;
use App\Schema\FaqSchema;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        Blog $blog,
        Inertia $inertia,
        FindRelatedBlogsAction $findRelatedBlogsAction,
        GetCommentsForItemAction $commentsForItemAction,
    ): Response {
        return $inertia
            ->title($blog->title)
            ->metaDescription($blog->meta_description)
            ->metaTags(explode(',', $blog->meta_tags))
            ->metaImage($blog->social_image)
            ->alternateMetas([
                'article:section' => 'Food',
                'article:published_time' => $blog->created_at,
                'article:modified_time' => $blog->updated_at,
                'article:author' => 'Coeliac Sanctuary',
                'article:tag' => $blog->meta_tags,
            ])
            ->schema(array_values(array_filter([
                $blog->schema()->toScript(),
                $blog->faqs->isNotEmpty() ? FaqSchema::make($blog->faqs)->toScript() : null,
            ])))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Blogs', route('blog.index')),
                new BreadcrumbItemData($blog->title),
            ]))
            ->metaFeed(route('blog.feed'))
            ->render('Blog/Show', [
                'blog' => new BlogShowResource($blog),
                'relatedBlogs' => RelatedBlogSimpleCardViewResource::collection(
                    $findRelatedBlogsAction->handle($blog->tags, $blog->primaryTag, $blog->id)
                ),
                'comments' => fn () => $commentsForItemAction->handle($blog),
            ]);
    }
}
