<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\Collections\Collection;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use App\Models\Recipes\RecipeNutrition;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductPrice;
use App\Models\Shop\ShopProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** @mixin TestCase */
trait SeedsWebsite
{
    protected function withAdminUser(): static
    {
        $this->create(User::class, ['email' => 'contact@coeliacsanctuary.co.uk']);

        return $this;
    }

    protected function withBlogs($count = 10, ?callable $then = null): static
    {
        Storage::fake('media');

        $this->build(Blog::class)
            ->count($count)
            ->sequence(fn (Sequence $sequence) => [
                'id' => $sequence->index + 1,
                'title' => "Blog {$sequence->index}",
                'created_at' => Carbon::now()->subDays($sequence->index),
            ])
            ->has($this->build(BlogTag::class)->count(3), 'tags')
            ->create()
            ->each(function (Blog $blog): void {
                $blog->addMedia(UploadedFile::fake()->image('blog.jpg'))->toMediaCollection('primary');
                $blog->addMedia(UploadedFile::fake()->image('blog.jpg'))->toMediaCollection('social');
            });

        if ($then) {
            $then();
        }

        return $this;
    }

    protected function withRecipes($count = 10, ?callable $then = null): static
    {
        Storage::fake('media');

        $this->build(Recipe::class)
            ->count($count)
            ->sequence(fn (Sequence $sequence) => [
                'id' => $sequence->index + 1,
                'title' => "Recipe {$sequence->index}",
                'created_at' => Carbon::now()->subDays($sequence->index),
            ])
            ->has($this->build(RecipeFeature::class), 'features')
            ->has($this->build(RecipeAllergen::class), 'allergens')
            ->has($this->build(RecipeMeal::class), 'meals')
            ->has($this->build(RecipeNutrition::class), 'nutrition')
            ->create()
            ->each(function (Recipe $recipe): void {
                $recipe->addMedia(UploadedFile::fake()->image('recipe.jpg'))->toMediaCollection('primary');
                $recipe->addMedia(UploadedFile::fake()->image('recipe.jpg'))->toMediaCollection('social');
            });

        if ($then) {
            $then();
        }

        return $this;
    }

    protected function withCollections($count = 10, ?callable $then = null): static
    {
        Storage::fake('media');

        $this->build(Collection::class)
            ->count($count)
            ->sequence(fn (Sequence $sequence) => [
                'id' => $sequence->index + 1,
                'title' => "Collection {$sequence->index}",
                'created_at' => Carbon::now()->subDays($sequence->index),
                'updated_at' => Carbon::now()->subDays($sequence->index),
            ])
            ->create()
            ->each(function (Collection $collection): void {
                $collection->addMedia(UploadedFile::fake()->image('collection.jpg'))->toMediaCollection('primary');
                $collection->addMedia(UploadedFile::fake()->image('collection.jpg'))->toMediaCollection('social');
            });

        if ($then) {
            $then();
        }

        return $this;
    }

    protected function withCategoriesAndProducts($categories = 5, $products = 2, $variants = 1, ?callable $then = null): static
    {
        Storage::fake('media');

        $this->build(ShopCategory::class)
            ->count($categories)
            ->sequence(fn (Sequence $sequence) => [
                'id' => $sequence->index + 1,
                'title' => "Category {$sequence->index}",
                'created_at' => Carbon::now()->subDays($sequence->index),
            ])
            ->create()
            ->each(function (ShopCategory $category) use ($products, $variants): void {
                $category->addMedia(UploadedFile::fake()->image('category.jpg'))->toMediaCollection('primary');
                $category->addMedia(UploadedFile::fake()->image('category.jpg'))->toMediaCollection('social');

                $this->build(ShopProduct::class)
                    ->count($products)
                    ->sequence(fn (Sequence $sequence) => [
                        'id' => $category->id . ($sequence->index + 1),
                        'title' => "Product {$sequence->index}",
                        'created_at' => Carbon::now()->subDays($sequence->index),
                    ])
                    ->has($this->build(ShopProductPrice::class), 'prices')
                    ->create()
                    ->each(function (ShopProduct $product) use ($category, $variants): void {
                        $product->addMedia(UploadedFile::fake()->image('product.jpg'))->toMediaCollection('primary');
                        $product->addMedia(UploadedFile::fake()->image('product.jpg'))->toMediaCollection('social');

                        $category->products()->attach($product);

                        $this->build(ShopProductVariant::class)
                            ->count($variants)
                            ->belongsToProduct($product)
                            ->sequence(fn (Sequence $sequence) => [
                                'id' => $product->id . ($sequence->index + 1),
                                'title' => "Variant {$sequence->index}",
                                'created_at' => Carbon::now()->subDays($sequence->index),
                            ])
                            ->create();
                    });
            });

        if ($then) {
            $then();
        }

        return $this;
    }
}
