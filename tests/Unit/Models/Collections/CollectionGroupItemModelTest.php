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
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionGroupItemModelTest extends TestCase
{
    protected CollectionGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $collection = $this->create(Collection::class);
        $this->group = $this->create(CollectionGroup::class, ['collection_id' => $collection->id]);
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
