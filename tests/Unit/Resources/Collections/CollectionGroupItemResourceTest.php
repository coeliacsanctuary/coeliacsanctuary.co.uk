<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Collections;

use App\Models\Blogs\Blog;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\Recipes\Recipe;
use App\Resources\Collections\CollectionGroupItemResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionGroupItemResourceTest extends TestCase
{
    protected CollectionGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCollections(1);

        $this->group = CollectionGroup::query()->first();
    }

    /** @return array<string, mixed> */
    protected function resource(CollectionGroupItem $item): array
    {
        return (new CollectionGroupItemResource($item->fresh()))->toArray(new Request());
    }

    #[Test]
    public function itReturnsThePublishedDateForARecipeRatherThanTheUpdatedDate(): void
    {
        $this->withRecipes(1);

        $recipe = Recipe::query()->first();
        $recipe->update(['updated_at' => Carbon::now()->addYear()]);

        $item = $this->build(CollectionGroupItem::class)
            ->forRecipe($recipe)
            ->create(['collection_group_id' => $this->group->id]);

        $resource = $this->resource($item);

        $this->assertSame('Recipe', $resource['type']);
        $this->assertSame($recipe->published, $resource['date']);
        $this->assertNotSame($recipe->lastUpdated, $resource['date']);
    }

    #[Test]
    public function itReturnsThePublishedDateForABlogRatherThanTheUpdatedDate(): void
    {
        $this->withBlogs(1);

        $blog = Blog::query()->first();
        $blog->update(['updated_at' => Carbon::now()->addYear()]);

        $item = $this->build(CollectionGroupItem::class)
            ->forBlog($blog)
            ->create(['collection_group_id' => $this->group->id]);

        $resource = $this->resource($item);

        $this->assertSame('Blog', $resource['type']);
        $this->assertSame($blog->published, $resource['date']);
        $this->assertNotSame($blog->lastUpdated, $resource['date']);
    }
}
