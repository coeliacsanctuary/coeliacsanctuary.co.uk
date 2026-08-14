<?php

declare(strict_types=1);

namespace Jpeters8889\CollectionItemSearch;

use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

readonly class SearchResult
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public ?string $imageUrl,
        public ?string $address,
        public ?string $info,
    ) {
    }

    public static function fromModel(Model $item, string $type): self
    {
        return match ($type) {
            Blog::class, Recipe::class => new self(
                id: $item->getKey(),
                title: (string) $item->getAttribute('title'),
                description: $item->getAttribute('meta_description'),
                imageUrl: $item instanceof HasMedia ? $item->getFirstMediaUrl('primary') : null,
                address: null,
                info: null,
            ),
            Eatery::class => new self(
                id: $item->getKey(),
                title: (string) $item->getAttribute('name'),
                description: null,
                imageUrl: null,
                address: $item->getAttribute('address'),
                info: $item->getAttribute('info'),
            ),
            NationwideBranch::class => new self(
                id: $item->getKey(),
                title: (string) $item->getAttribute('name'),
                description: null,
                imageUrl: null,
                address: $item->getAttribute('address'),
                info: $item->relationLoaded('eatery') && $item->eatery
                    ? $item->eatery->getAttribute('info')
                    : null,
            ),
            default => new self(
                id: $item->getKey(),
                title: (string) ($item->getAttribute('title') ?? $item->getAttribute('name') ?? (string) $item->getKey()),
                description: null,
                imageUrl: null,
                address: null,
                info: null,
            ),
        };
    }
}
