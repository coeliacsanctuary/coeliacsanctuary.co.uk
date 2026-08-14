<?php

declare(strict_types=1);

namespace App\Http\Controllers\Blogs;

use App\Actions\Blogs\GetBlogsForBlogIndexAction;
use App\Actions\Blogs\GetBlogTagsAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\Blogs\BlogTag;
use Illuminate\Support\Str;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Inertia $inertia,
        GetBlogsForBlogIndexAction $getBlogsForBlogIndexAction,
        GetBlogTagsAction $getBlogTagsAction,
        GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction,
        BlogTag $tag
    ): Response {
        return $inertia
            ->title('Gluten Free Blogs' . ($tag->exists ? " tagged with {$tag->tag}" : ''))
            ->metaDescription($tag->exists
                ? "Coeliac Sanctuary gluten free blogs tagged with {$tag->tag} — coeliac news, new product launches, free from reviews, and life with coeliac disease."
                : 'Gluten free and coeliac blogs from Coeliac Sanctuary — coeliac news, new gluten free product launches, free from range reviews, and life with coeliac disease.')
            ->metaTags(['coeliac sanctuary blog', 'blog', 'coeliac blog', 'gluten free blog', ...($tag->exists ? ["{$tag->tag} blogs"] : [])])
            ->metaImage($getOpenGraphImageForRouteAction->handle('blog'))
            ->metaFeed(route('blog.feed'))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Blogs'),
            ]))
            ->render('Blog/Index', [
                'blogs' => fn () => $getBlogsForBlogIndexAction->handle($tag),
                'tags' => Inertia::defer(fn () => $getBlogTagsAction->handle()),
                'activeTag' => $tag->exists ? Str::title($tag->tag) : null,
            ]);
    }
}
