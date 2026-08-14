<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Collections;

use App\Actions\Collections\GetCollectionsForIndexAction;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\ResourceCollections\Collections\CollectionListCollection;
use App\Resources\Collections\CollectionDetailCardViewResource;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetCollectionsForIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->withCollections(15);
    }

    #[Test]
    public function itReturnsACollectionListCollection(): void
    {
        $this->assertInstanceOf(
            CollectionListCollection::class,
            $this->callAction(GetCollectionsForIndexAction::class),
        );
    }

    #[Test]
    public function itIsAPaginatedCollection(): void
    {
        $collections = $this->callAction(GetCollectionsForIndexAction::class);

        $this->assertInstanceOf(LengthAwarePaginator::class, $collections->resource);
    }

    #[Test]
    public function itReturns12ItemsPerPageByDefault(): void
    {
        $this->assertCount(12, $this->callAction(GetCollectionsForIndexAction::class));
    }

    #[Test]
    public function itCanHaveADifferentPageLimitSpecified(): void
    {
        $this->assertCount(5, $this->callAction(GetCollectionsForIndexAction::class, perPage: 5));
    }

    #[Test]
    public function eachItemInThePageIsACollectionDetailCardViewResource(): void
    {
        $resource = $this->callAction(GetCollectionsForIndexAction::class)->resource->first();

        $this->assertInstanceOf(CollectionDetailCardViewResource::class, $resource);
    }

    #[Test]
    public function itLoadsTheMediaAndTagsRelationship(): void
    {
        $this->assertTrue($this->firstCollection()->relationLoaded('media'));
    }

    #[Test]
    public function itDoesntEagerLoadTheCollectionGroups(): void
    {
        $this->assertFalse($this->firstCollection()->relationLoaded('groups'));
    }

    #[Test]
    public function itCountsRecipesBlogsAndEateriesSeparately(): void
    {
        $this->build(CollectionGroupItem::class)
            ->forRecipe($this->create(Recipe::class))
            ->create(['collection_group_id' => $this->group()->id]);

        $this->build(CollectionGroupItem::class)
            ->forRecipe($this->create(Recipe::class))
            ->create(['collection_group_id' => $this->group()->id]);

        $this->build(CollectionGroupItem::class)
            ->forBlog($this->create(Blog::class))
            ->create(['collection_group_id' => $this->group()->id]);

        $this->build(CollectionGroupItem::class)
            ->forEatery($this->create(Eatery::class))
            ->create(['collection_group_id' => $this->group()->id]);

        $collection = $this->firstCollection();

        $this->assertEquals(2, $collection->recipes_count);
        $this->assertEquals(1, $collection->blogs_count);
        $this->assertEquals(1, $collection->eateries_count);
    }

    #[Test]
    public function itCountsNationwideBranchesAsEateries(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->build(CollectionGroupItem::class)
            ->forEatery($eatery)
            ->create(['collection_group_id' => $this->group()->id]);

        $this->build(CollectionGroupItem::class)
            ->forNationwideBranch($this->build(NationwideBranch::class)->forEatery($eatery)->create())
            ->create(['collection_group_id' => $this->group()->id]);

        $this->assertEquals(2, $this->firstCollection()->eateries_count);
    }

    #[Test]
    public function itDoesntCountItemsThatArentLive(): void
    {
        $this->build(CollectionGroupItem::class)
            ->forBlog($this->create(Blog::class))
            ->create(['collection_group_id' => $this->group()->id]);

        $this->build(CollectionGroupItem::class)
            ->forBlog($this->build(Blog::class)->notLive()->create())
            ->create(['collection_group_id' => $this->group()->id]);

        $this->assertEquals(1, $this->firstCollection()->blogs_count);
    }

    #[Test]
    public function itDoesntCountItemsWhoseAssociatedModelNoLongerExists(): void
    {
        $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group()->id,
            'item_id' => 12345,
            'item_type' => Blog::class,
        ]);

        $this->assertEquals(0, $this->firstCollection()->blogs_count);
    }

    protected function group(): CollectionGroup
    {
        /** @var CollectionGroup $group */
        $group = CollectionGroup::query()->where('collection_id', 1)->firstOrFail();

        return $group;
    }

    protected function firstCollection(): Collection
    {
        /** @var CollectionDetailCardViewResource $resource */
        $resource = $this->callAction(GetCollectionsForIndexAction::class)->resource->first();

        /** @var Collection $collection */
        $collection = $resource->resource;

        return $collection;
    }
}
