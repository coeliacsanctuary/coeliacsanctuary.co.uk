<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blogs\Blog;
use App\Models\Collections\CollectionGroupItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;

class CollectionGroupItemFactory extends Factory
{
    protected $model = CollectionGroupItem::class;

    public function definition(): array
    {
        return [
            'item_title' => null,
            'item_description' => null,
        ];
    }

    public function forBlog(Blog $blog): self
    {
        return $this->state(fn () => [
            'item_id' => $blog->id,
            'item_type' => Blog::class,
        ]);
    }

    public function forRecipe(Recipe $recipe): self
    {
        return $this->state(fn () => [
            'item_id' => $recipe->id,
            'item_type' => Recipe::class,
        ]);
    }

    public function forEatery(Eatery $eatery): self
    {
        return $this->state(fn () => [
            'item_id' => $eatery->id,
            'item_type' => Eatery::class,
        ]);
    }

    public function forNationwideBranch(NationwideBranch $branch): self
    {
        return $this->state(fn () => [
            'item_id' => $branch->id,
            'item_type' => NationwideBranch::class,
        ]);
    }
}
