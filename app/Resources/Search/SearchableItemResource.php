<?php

declare(strict_types=1);

namespace App\Resources\Search;

use App\Contracts\Search\IsSearchable;
use App\Enums\EatingOut\EateryType;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\Models\Shop\ShopProduct;
use App\Support\Helpers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin IsSearchable */
class SearchableItemResource extends JsonResource
{
    /** @var Blog | Recipe | Eatery | NationwideBranch | ShopProduct */
    public $resource;

    public function toArray(Request $request): array
    {
        $title = match ($this->resource::class) {
            Blog::class => Str::of($this->resource->title)->replace('&quot;', '"'),
            Recipe::class, ShopProduct::class => $this->resource->title,
            Eatery::class => "{$this->resource->name}, {$this->resource->short_location}",
            NationwideBranch::class => ($this->resource->name ?: $this->resource->eatery->name) . ", {$this->resource->short_location}",
            default => throw new Exception('Unknown search class'),
        };

        $description = match ($this->resource::class) {
            Blog::class, Recipe::class, ShopProduct::class => $this->resource->meta_description,
            Eatery::class => $this->resource->type_id !== EateryType::ATTRACTION->value
                ? $this->resource->info
                : $this->resource->restaurants->map(fn (EateryAttractionRestaurant $restaurant): array => [
                    'name' => $restaurant->restaurant_name,
                    'info' => $restaurant->info,
                ]),
            NationwideBranch::class => $this->resource->eatery->info,
            default => throw new Exception('Unknown search class'),
        };

        $image = match ($this->resource::class) {
            Blog::class, Recipe::class, ShopProduct::class => $this->resource->main_image,
            Eatery::class, NationwideBranch::class => ['lat' => $this->resource->lat, 'lng' => $this->resource->lng],
            default => throw new Exception('Unknown search class'),
        };

        $link = match ($this->resource::class) {
            Blog::class, Recipe::class, ShopProduct::class => $this->resource->link,
            Eatery::class, NationwideBranch::class => $this->resource->link(),
            default => throw new Exception('Unknown search class'),
        };

        $distance = null;

        if ($this->resource instanceof Eatery || $this->resource instanceof NationwideBranch) {
            if ($this->resource->hasAttribute('_resDistance') && $this->resource->getAttribute('_resDistance') !== null) {
                /** @var float $rawDistance */
                $rawDistance = $this->resource->getAttribute('_resDistance');

                $distance = Helpers::metersToMiles($rawDistance);
            }
        }

        return [
            'type' => Str::headline(class_basename($this->resource::class)),
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'link' => $link,
            'score' => $this->resource->getAttribute('_score'),
            'distance' => $distance,
        ];
    }
}
