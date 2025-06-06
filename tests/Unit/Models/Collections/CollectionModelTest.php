<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Collections;

use App\Jobs\OpenGraphImages\CreateCollectionIndexPageOpenGraphImageJob;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Scopes\LiveScope;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\CanBePublishedTestTrait;
use Tests\Concerns\DisplaysMediaTestTrait;
use Tests\Concerns\LinkableModelTestTrait;
use Tests\TestCase;

class CollectionModelTest extends TestCase
{
    use CanBePublishedTestTrait;
    use DisplaysMediaTestTrait;
    use LinkableModelTestTrait;

    protected Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCollections(1);

        $this->collection = Collection::query()->first();

        $this->setUpDisplaysMediaTest(fn () => $this->create(Collection::class));

        $this->setUpLinkableModelTest(fn (array $params) => $this->create(Collection::class, $params));

        $this->setUpCanBePublishedModelTest(fn (array $params = []) => $this->create(Collection::class, $params));
    }

    #[Test]
    public function itHasTheLiveScopeApplied(): void
    {
        $this->assertTrue(Collection::hasGlobalScope(LiveScope::class));
    }

    #[Test]
    public function itDispatchesTheCreateOpenGraphImageJobWhenSaved(): void
    {
        config()->set('coeliac.generate_og_images', true);

        Bus::fake();

        $this->create(Collection::class);

        Bus::assertDispatched(CreateCollectionIndexPageOpenGraphImageJob::class);
    }

    #[Test]
    public function itCanHaveItemsAddedToTheCollection(): void
    {
        $this->assertEmpty($this->collection->items);

        /** @var Blog $blog */
        $blog = $this->create(Blog::class);

        $this->collection->addItem($blog, $blog->meta_description)->refresh();

        $this->assertNotEmpty($this->collection->items);
    }

    #[Test]
    public function itClearsCacheWhenARowIsCreated(): void
    {
        foreach (config('coeliac.cacheable.collections') as $key) {
            Cache::put($key, 'foo');

            $this->create(Collection::class);

            $this->assertFalse(Cache::has($key));
        }
    }

    #[Test]
    public function itClearsCacheWhenARowIsUpdated(): void
    {
        foreach (config('coeliac.cacheable.collections') as $key) {
            $collection = $this->create(Collection::class);

            Cache::put($key, 'foo');

            $collection->update();

            $this->assertFalse(Cache::has($key));
        }
    }
}
