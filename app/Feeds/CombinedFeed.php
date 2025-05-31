<?php

declare(strict_types=1);

namespace App\Feeds;

use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Model;

/** @extends Feed<Blog | Recipe> */
class CombinedFeed extends Feed
{
    protected function formatItem(Model $item): array
    {
        return [
            'title' => [
                'cdata' => true,
                'value' => $item->title,
            ],
            'link' => [
                'value' => $item->absolute_link,
            ],
            'guid' => [
                'value' => md5('blog-' . $item->id),
            ],
            'description' => [
                'cdata' => true,
                'value' => $item->meta_description,
            ],
            'author' => [
                'value' => 'contact@coeliacsanctuary.co.uk (Coeliac Sanctuary)',
            ],
            'comments' => [
                'value' => $item->absolute_link . '#comments',
            ],
            'enclosure' => [
                'value' => '',
                'short' => true,
                'params' => [
                    'url' => $item->main_image,
                    'type' => 'image/*',
                ],
            ],
            'pubDate' => [
                'value' => $item->created_at->toRfc822String(),
            ],
        ];

    }

    protected function feedTitle(): string
    {
        return 'Coeliac Sanctuary RSS Feed';
    }

    protected function linkRoot(): string
    {
        return route('home');
    }

    protected function feedDescription(): string
    {
        return 'Blogs, Reviews and Recipes from CoeliacSanctuary.co.uk';
    }
}
