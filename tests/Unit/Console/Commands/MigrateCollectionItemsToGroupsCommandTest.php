<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\OneTime\MigrateCollectionItemsToGroupsCommand;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MigrateCollectionItemsToGroupsCommandTest extends TestCase
{
    #[Test]
    public function itCreatesOneGroupPerCollection(): void
    {
        $this->create(Collection::class);
        $this->create(Collection::class);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $this->assertDatabaseCount(CollectionGroup::class, 2);
    }

    #[Test]
    public function itCreatesAGroupForCollectionsWithNoItems(): void
    {
        $collection = $this->create(Collection::class);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $this->assertDatabaseCount(CollectionGroup::class, 1);

        $group = CollectionGroup::first();

        $this->assertEquals($collection->id, $group->collection_id);
        $this->assertNull($group->title);
        $this->assertNull($group->body);
    }

    #[Test]
    public function theCreatedGroupHasNullTitleAndBody(): void
    {
        $this->create(Collection::class);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $group = CollectionGroup::first();

        $this->assertNull($group->title);
        $this->assertNull($group->body);
    }

    #[Test]
    public function theCreatedGroupInheritsTimestampsFromTheCollection(): void
    {
        $collection = $this->create(Collection::class, [
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-06-01 12:00:00',
        ]);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $group = CollectionGroup::first();

        $this->assertEquals($collection->created_at, $group->created_at);
        $this->assertEquals($collection->updated_at, $group->updated_at);
    }

    #[Test]
    public function itMovesItemsToTheCorrectGroup(): void
    {
        $collection = $this->create(Collection::class);

        DB::table('collection_items')->insert([
            'collection_id' => $collection->id,
            'item_id' => 1,
            'item_type' => 'App\Models\Blogs\Blog',
            'position' => 1,
            'description' => 'Some description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $group = CollectionGroup::first();

        $this->assertDatabaseCount(CollectionGroupItem::class, 1);

        $item = CollectionGroupItem::first();

        $this->assertEquals($group->id, $item->collection_group_id);
    }

    #[Test]
    public function itPreservesItemIdTypeAndPosition(): void
    {
        $collection = $this->create(Collection::class);

        DB::table('collection_items')->insert([
            'collection_id' => $collection->id,
            'item_id' => 42,
            'item_type' => 'App\Models\Recipes\Recipe',
            'position' => 3,
            'description' => 'desc',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $item = CollectionGroupItem::first();

        $this->assertEquals(42, $item->item_id);
        $this->assertEquals('App\Models\Recipes\Recipe', $item->item_type);
        $this->assertEquals(3, $item->position);
    }

    #[Test]
    public function migratedItemsHaveNullOverrideFields(): void
    {
        $collection = $this->create(Collection::class);

        DB::table('collection_items')->insert([
            'collection_id' => $collection->id,
            'item_id' => 1,
            'item_type' => 'App\Models\Blogs\Blog',
            'position' => 1,
            'description' => 'desc',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $item = CollectionGroupItem::first();

        $this->assertNull($item->item_title);
        $this->assertNull($item->item_description);
    }

    #[Test]
    public function migratedItemsInheritTimestampsFromTheOriginalRow(): void
    {
        $collection = $this->create(Collection::class);

        DB::table('collection_items')->insert([
            'collection_id' => $collection->id,
            'item_id' => 1,
            'item_type' => 'App\Models\Blogs\Blog',
            'position' => 1,
            'description' => 'desc',
            'created_at' => '2023-03-15 09:00:00',
            'updated_at' => '2023-09-20 14:30:00',
        ]);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $item = CollectionGroupItem::first();

        $this->assertEquals('2023-03-15 09:00:00', $item->created_at);
        $this->assertEquals('2023-09-20 14:30:00', $item->updated_at);
    }

    #[Test]
    public function itemsFromDifferentCollectionsGoToTheirOwnGroup(): void
    {
        $collectionA = $this->create(Collection::class);
        $collectionB = $this->create(Collection::class);

        DB::table('collection_items')->insert([
            ['collection_id' => $collectionA->id, 'item_id' => 1, 'item_type' => 'App\Models\Blogs\Blog', 'position' => 1, 'description' => '', 'created_at' => now(), 'updated_at' => now()],
            ['collection_id' => $collectionB->id, 'item_id' => 2, 'item_type' => 'App\Models\Blogs\Blog', 'position' => 1, 'description' => '', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)->run();

        $groupA = CollectionGroup::where('collection_id', $collectionA->id)->first();
        $groupB = CollectionGroup::where('collection_id', $collectionB->id)->first();

        $this->assertDatabaseCount(CollectionGroupItem::class, 2);
        $this->assertEquals($groupA->id, CollectionGroupItem::where('item_id', 1)->value('collection_group_id'));
        $this->assertEquals($groupB->id, CollectionGroupItem::where('item_id', 2)->value('collection_group_id'));
    }

    #[Test]
    public function itReportsCorrectCounts(): void
    {
        $this->create(Collection::class);
        $collectionB = $this->create(Collection::class);

        DB::table('collection_items')->insert([
            ['collection_id' => $collectionB->id, 'item_id' => 1, 'item_type' => 'App\Models\Blogs\Blog', 'position' => 1, 'description' => '', 'created_at' => now(), 'updated_at' => now()],
            ['collection_id' => $collectionB->id, 'item_id' => 2, 'item_type' => 'App\Models\Blogs\Blog', 'position' => 2, 'description' => '', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->artisan(MigrateCollectionItemsToGroupsCommand::class)
            ->expectsOutputToContain('Groups created: 2')
            ->expectsOutputToContain('Items migrated: 2')
            ->run();
    }
}
