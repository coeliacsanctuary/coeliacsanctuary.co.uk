<?php

declare(strict_types=1);

namespace App\Feeds;

use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Model;

/** @extends Feed<Recipe> */
class RecipeFeed extends Feed
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
                'value' => md5('recipe-' . $item->id),
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
        return 'Coeliac Sanctuary Recipe RSS Feed';
    }

    protected function linkRoot(): string
    {
        return route('recipe.index');
    }

    protected function feedDescription(): string
    {
        return 'Gluten free recipes from CoeliacSanctuary.co.uk';
    }
}
