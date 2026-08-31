<?php

declare(strict_types=1);

namespace App\Filament\Dto;

use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

readonly class CollectionItemDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description = null,
        public ?string $imageUrl = null,
        public ?string $address = null,
        public ?string $info = null,
    ) {
        //
    }

    /** @param class-string<Model> $type */
    public static function fromModel(Model $item, string $type): self
    {
        return match ($type) {
            Blog::class, Recipe::class => new self(
                id: $item->getKey(),
                title: (string) $item->getAttribute('title'),
                description: $item->getAttribute('meta_description'),
                imageUrl: $item instanceof HasMedia ? ($item->getFirstMediaUrl('primary') ?: null) : null,
            ),
            Eatery::class => new self(
                id: $item->getKey(),
                title: (string) $item->getAttribute('name'),
                address: $item->getAttribute('address'),
                info: $item->getAttribute('info'),
            ),
            NationwideBranch::class => new self(
                id: $item->getKey(),
                title: (string) $item->getAttribute('name'),
                address: $item->getAttribute('address'),
                info: $item instanceof NationwideBranch ? $item->eatery?->info : null,
            ),
            default => new self(
                id: $item->getKey(),
                title: (string) ($item->getAttribute('title') ?? $item->getAttribute('name') ?? $item->getKey()),
            ),
        };
    }

    public function subtitle(): ?string
    {
        $parts = collect([$this->description, $this->address, $this->info])
            ->filter()
            ->map(fn (string $part): string => Str::limit(mb_trim(strip_tags($part)), 80))
            ->filter();

        return $parts->isEmpty() ? null : $parts->implode(' — ');
    }
}
