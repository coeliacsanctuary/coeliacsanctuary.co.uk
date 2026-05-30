<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Collections;

use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionGroupModelTest extends TestCase
{
    protected Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = $this->create(Collection::class);
    }

    #[Test]
    public function itBelongsToACollection(): void
    {
        $group = $this->create(CollectionGroup::class, ['collection_id' => $this->collection->id]);

        $this->assertTrue($group->collection->is($this->collection));
    }

    #[Test]
    public function itHasManyItems(): void
    {
        $group = $this->create(CollectionGroup::class, ['collection_id' => $this->collection->id]);

        $this->assertEmpty($group->items);

        $this->create(CollectionGroupItem::class, ['collection_group_id' => $group->id, 'item_id' => 1, 'item_type' => 'App\Models\Blogs\Blog']);

        $this->assertCount(1, $group->refresh()->items);
    }

    #[Test]
    public function itemsAreOrderedByPosition(): void
    {
        $group = $this->create(CollectionGroup::class, ['collection_id' => $this->collection->id]);

        $first = $this->create(CollectionGroupItem::class, ['collection_group_id' => $group->id, 'item_id' => 1, 'item_type' => 'App\Models\Blogs\Blog']);
        $second = $this->create(CollectionGroupItem::class, ['collection_group_id' => $group->id, 'item_id' => 2, 'item_type' => 'App\Models\Blogs\Blog']);

        $this->assertLessThan($second->position, $first->position);
    }

    #[Test]
    public function itAssignsPositionOnCreate(): void
    {
        $groupOne = $this->create(CollectionGroup::class, ['collection_id' => $this->collection->id]);
        $groupTwo = $this->create(CollectionGroup::class, ['collection_id' => $this->collection->id]);

        $this->assertEquals(1, $groupOne->position);
        $this->assertEquals(2, $groupTwo->position);
    }

    #[Test]
    public function positionIsScopedToCollection(): void
    {
        $otherCollection = $this->create(Collection::class);

        $groupInFirst = $this->create(CollectionGroup::class, ['collection_id' => $this->collection->id]);
        $groupInSecond = $this->create(CollectionGroup::class, ['collection_id' => $otherCollection->id]);

        $this->assertEquals(1, $groupInFirst->position);
        $this->assertEquals(1, $groupInSecond->position);
    }
}
