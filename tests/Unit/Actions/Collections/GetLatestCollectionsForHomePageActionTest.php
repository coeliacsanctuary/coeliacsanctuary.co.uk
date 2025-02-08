<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Collections;

use App\Actions\Collections\GetLatestCollectionsForHomepageAction;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
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

        Blog::query()->take(3)->get()->each(function (Blog $blog) use ($collection): void {
            $collection->addItem($blog, $blog->description);
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

        $this->build(Blog::class)->count(5)->create()->each(function (Blog $blog) use ($collection): void {
            $collection->addItem($blog, $blog->description);
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
        // collections and media/item relation;
        $this->assertCount(3, DB::getQueryLog());

        $this->callAction(GetLatestCollectionsForHomepageAction::class);

        $this->assertCount(3, DB::getQueryLog());
    }
}
