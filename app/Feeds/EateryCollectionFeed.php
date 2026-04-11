<?php

declare(strict_types=1);

namespace App\Feeds;

use App\Models\EatingOut\EateryCollection;
use Illuminate\Database\Eloquent\Model;

/** @extends Feed<EateryCollection> */
class EateryCollectionFeed extends Feed
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
                'value' => md5('eatery-collection-' . $item->id),
            ],
            'description' => [
                'cdata' => true,
                'value' => $item->meta_description,
            ],
            'author' => [
                'value' => 'contact@coeliacsanctuary.co.uk (Coeliac Sanctuary)',
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
        return 'Coeliac Sanctuary Eatery Collections RSS Feed';
    }

    protected function linkRoot(): string
    {
        return route('eating-out.collections.index');
    }

    protected function feedDescription(): string
    {
        return 'Eating Out Collections from CoeliacSanctuary.co.uk';
    }
}
