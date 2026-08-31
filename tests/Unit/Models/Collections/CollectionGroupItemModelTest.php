<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Collections;

use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionGroupItemModelTest extends TestCase
{
    protected Collection $collection;

    protected CollectionGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = $this->create(Collection::class);
        $this->group = $this->create(CollectionGroup::class, ['collection_id' => $this->collection->id]);
    }

    #[Test]
    public function itBelongsToAGroup(): void
    {
        $item = $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group->id,
            'item_id' => 1,
            'item_type' => Blog::class,
        ]);

        $this->assertTrue($item->group->is($this->group));
    }

    #[Test]
    public function itCanMorphToABlog(): void
    {
        $blog = $this->create(Blog::class);

        $item = $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group->id,
            'item_id' => $blog->id,
            'item_type' => Blog::class,
        ]);

        $this->assertInstanceOf(Blog::class, $item->item);
        $this->assertTrue($item->item->is($blog));
    }

    #[Test]
    public function itCanMorphToARecipe(): void
    {
        $recipe = $this->create(Recipe::class);

        $item = $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group->id,
            'item_id' => $recipe->id,
            'item_type' => Recipe::class,
        ]);

        $this->assertInstanceOf(Recipe::class, $item->item);
        $this->assertTrue($item->item->is($recipe));
    }

    #[Test]
    public function itCanMorphToAnEatery(): void
    {
        $eatery = $this->create(Eatery::class);

        $item = $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group->id,
            'item_id' => $eatery->id,
            'item_type' => Eatery::class,
        ]);

        $this->assertInstanceOf(Eatery::class, $item->item);
        $this->assertTrue($item->item->is($eatery));
    }

    #[Test]
    public function itCanMorphToANationwideBranch(): void
    {
        $branch = $this->create(NationwideBranch::class);

        $item = $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group->id,
            'item_id' => $branch->id,
            'item_type' => NationwideBranch::class,
        ]);

        $this->assertInstanceOf(NationwideBranch::class, $item->item);
        $this->assertTrue($item->item->is($branch));
    }

    #[Test]
    public function itTouchesItsGroupAndCollectionWhenSaved(): void
    {
        $item = $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group->id,
            'item_id' => 1,
            'item_type' => Blog::class,
        ]);

        $groupUpdatedAt = $this->group->refresh()->updated_at;
        $collectionUpdatedAt = $this->collection->refresh()->updated_at;

        $this->travel(1)->hour();

        $item->update(['item_title' => 'My favourite loaf']);

        $this->assertTrue($this->group->refresh()->updated_at->greaterThan($groupUpdatedAt));
        $this->assertTrue($this->collection->refresh()->updated_at->greaterThan($collectionUpdatedAt));
    }

    #[Test]
    public function itSavesWithLazyLoadingDisabled(): void
    {
        $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group->id,
            'item_id' => 1,
            'item_type' => Blog::class,
        ]);

        $this->create(CollectionGroupItem::class, [
            'collection_group_id' => $this->group->id,
            'item_id' => 2,
            'item_type' => Blog::class,
        ]);

        Model::preventLazyLoading();

        try {
            $item = CollectionGroupItem::query()->get()->first();

            $item->update(['item_title' => 'My favourite loaf']);
        } finally {
            Model::preventLazyLoading(false);
        }

        $this->assertSame('My favourite loaf', $item->refresh()->item_title);
    }

    #[Test]
    public function itAssignsPositionOnCreate(): void
    {
        $itemOne = $this->create(CollectionGroupItem::class, ['collection_group_id' => $this->group->id, 'item_id' => 1, 'item_type' => Blog::class]);
        $itemTwo = $this->create(CollectionGroupItem::class, ['collection_group_id' => $this->group->id, 'item_id' => 2, 'item_type' => Blog::class]);

        $this->assertEquals(1, $itemOne->position);
        $this->assertEquals(2, $itemTwo->position);
    }

    #[Test]
    public function positionIsScopedToGroup(): void
    {
        $otherCollection = $this->create(Collection::class);
        $otherGroup = $this->create(CollectionGroup::class, ['collection_id' => $otherCollection->id]);

        $itemInFirst = $this->create(CollectionGroupItem::class, ['collection_group_id' => $this->group->id, 'item_id' => 1, 'item_type' => Blog::class]);
        $itemInSecond = $this->create(CollectionGroupItem::class, ['collection_group_id' => $otherGroup->id, 'item_id' => 1, 'item_type' => Blog::class]);

        $this->assertEquals(1, $itemInFirst->position);
        $this->assertEquals(1, $itemInSecond->position);
    }
}
