<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Blogs\Blog;
use App\Models\Collections\CollectionGroupItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RuntimeException;

/**
 * @mixin CollectionGroupItem
 */
class CollectionGroupItemResource extends JsonResource
{
    /** @return array */
    public function toArray(Request $request)
    {
        /** @var Blog | Recipe | Eatery | NationwideBranch $collectible */
        $collectible = $this->item;

        return [
            'type' => class_basename($collectible),
            ...$this->renderCollectible($collectible),
        ];
    }

    protected function renderCollectible(Blog|Recipe|Eatery|NationwideBranch $collectible): array
    {
        return match ($collectible::class) {
            Blog::class => $this->renderBlog($collectible),
            Recipe::class => $this->renderRecipe($collectible),
            Eatery::class => $this->renderEatery($collectible),
            NationwideBranch::class => $this->renderBranch($collectible),
            default => throw new RuntimeException('Unknown collectible class.'),
        };
    }

    protected function renderBlog(Blog $blog): array
    {
        return [
            'title' => $this->item_title ?? $blog->title,
            'description' => $this->item_description ?? $blog->meta_description,
            'image' => $blog->main_image_as_webp ?? $blog->main_image,
            'header_image_alt_text' => $blog->header_image_alt_text,
            'date' => $blog->lastUpdated,
            'link' => $blog->link,
        ];
    }

    protected function renderRecipe(Recipe $recipe): array
    {
        return [
            'title' => $this->item_title ?? $recipe->title,
            'description' => $this->item_description ?? $recipe->meta_description,
            'image' => $recipe->main_image_as_webp ?? $recipe->main_image,
            'header_image_alt_text' => $recipe->header_image_alt_text,
            'square_image' => $recipe->square_image_as_webp ?? $recipe->square_image,
            'date' => $recipe->lastUpdated,
            'link' => $recipe->link,
        ];
    }

    protected function renderEatery(Eatery $eatery): array
    {
        return [
            'name' => $this->item_title ?? $eatery->name,
            'full_location' => $eatery->full_location,
            'description' => $this->item_description ?? $eatery->info,
            'location' => [
                'address' => collect(explode("\n", $eatery->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $eatery->lat,
                'lng' => $eatery->lng,
            ],
            'reviews' => [
                'number' => $eatery->reviews->count(),
                'average' => $eatery->average_rating,
            ],
            'link' => $eatery->link(),
        ];
    }

    protected function renderBranch(NationwideBranch $branch): array
    {
        return [
            'name' => $this->item_title ?? ($branch->name ? "{$branch->name} - ({$branch->eatery->name})" : $branch->eatery->name),
            'full_location' => $branch->full_location,
            'description' => $this->item_description ?? $branch->eatery->info,
            'location' => [
                'address' => collect(explode("\n", $branch->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $branch->lat,
                'lng' => $branch->lng,
            ],
            'reviews' => [
                'number' => $branch->reviews->count(),
                'average' => $branch->average_rating,
            ],
            'link' => $branch->link(),
        ];

    }
}
