<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Collections;

use App\Actions\Collections\GetLatestCollectionsForHomepageAction;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Resources\Collections\CollectedItemSimpleCardViewResource;
use App\Resources\Collections\CollectionSimpleCardViewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetLatestCollectionsForHomePageActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withCollections(2);
    }

    #[Test]
    public function itCanReturnACollectionOfCollections(): void
    {
        $this->assertInstanceOf(AnonymousResourceCollection::class, $this->callAction(GetLatestCollectionsForHomepageAction::class));
    }

    #[Test]
    public function itDoesntLoadAnyCollectionsIfNoneAreSetToDisplayOnTheHomepage(): void
    {
        $this->assertCount(0, $this->callAction(GetLatestCollectionsForHomepageAction::class));
    }

    #[Test]
    public function itReturnsTheCollectionsAsACardResource(): void
    {
        Collection::query()->update(['display_on_homepage' => true]);

        $this->callAction(GetLatestCollectionsForHomepageAction::class)->each(function ($item): void {
            $this->assertInstanceOf(CollectionSimpleCardViewResource::class, $item);
        });
    }

    #[Test]
    public function itReturnsTheCollectedItemsWithTheCollectionResource(): void
    {
        /** @var Collection $collection */
        $collection = Collection::query()->first();

        $collection->update(['display_on_homepage' => true]);

        /** @var CollectionGroup $group */
        $group = $this->create(CollectionGroup::class, ['collection_id' => $collection->id]);

        $this->build(Blog::class)->count(3)->create()->each(function (Blog $blog) use ($group): void {
            $this->build(CollectionGroupItem::class)->forBlog($blog)->create(['collection_group_id' => $group->id]);
        });

        $collectionResource = $this->callAction(GetLatestCollectionsForHomepageAction::class)[0]->toArray(request());

        $this->assertInstanceOf(AnonymousResourceCollection::class, $collectionResource['items']);

        $collectionResource['items']->each(function ($item): void {
            $this->assertInstanceOf(CollectedItemSimpleCardViewResource::class, $item);
        });
    }

    #[Test]
    public function itOnlyReturnsThreeCollectedItemsWithTheResource(): void
    {
        /** @var Collection $collection */
        $collection = Collection::query()->first();

        $collection->update(['display_on_homepage' => true]);

        /** @var CollectionGroup $group */
        $group = $collection->groups->first();

        $this->build(Blog::class)->count(5)->create()->each(function (Blog $blog) use ($group): void {
            $this->build(CollectionGroupItem::class)->forBlog($blog)->create(['collection_group_id' => $group->id]);
        });

        $collectionResource = $this->callAction(GetLatestCollectionsForHomepageAction::class)[0]->toArray(request());

        $this->assertCount(3, $collectionResource['items']);
    }

    #[Test]
    public function itCachesTheCollections(): void
    {
        Collection::query()->update(['display_on_homepage' => true]);

        $this->assertFalse(Cache::has(config('coeliac.cacheable.collections.home')));

        $collections = $this->callAction(GetLatestCollectionsForHomepageAction::class);

        $this->assertTrue(Cache::has(config('coeliac.cacheable.collections.home')));
        $this->assertSame($collections, Cache::get(config('coeliac.cacheable.collections.home')));
    }

    #[Test]
    public function itLoadsTheCollectionsFromTheCache(): void
    {
        Collection::query()->update(['display_on_homepage' => true]);

        DB::enableQueryLog();

        $this->callAction(GetLatestCollectionsForHomepageAction::class);
        // collections, groups, group items, and media relations
        $this->assertCount(4, DB::getQueryLog());

        $this->callAction(GetLatestCollectionsForHomepageAction::class);

        $this->assertCount(4, DB::getQueryLog());
    }
}
