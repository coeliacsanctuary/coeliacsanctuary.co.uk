<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Collections;

use App\Enums\Collections\CollectionDisplayType;
use App\Models\Collections\Collection;
use App\Resources\Collections\CollectionShowResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionShowResourceTest extends TestCase
{
    protected Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCollections(1);

        $this->collection = Collection::query()->first();
    }

    /** @return array<string, mixed> */
    protected function resource(): array
    {
        return (new CollectionShowResource($this->collection->fresh()))->toArray(new Request());
    }

    #[Test]
    public function itDoesntReturnAnUpdatedDateWhenTheCollectionHasNotBeenUpdatedSincePublishing(): void
    {
        $this->assertNull($this->resource()['updated']);
    }

    #[Test]
    public function itReturnsTheUpdatedDateWhenTheCollectionHasBeenUpdatedSincePublishing(): void
    {
        $this->collection->update(['updated_at' => Carbon::now()->addYear()]);

        $resource = $this->resource();

        $this->assertNotNull($resource['updated']);
        $this->assertNotSame($resource['published'], $resource['updated']);
    }

    #[Test]
    public function itDefaultsToTheGridDisplayType(): void
    {
        $this->assertSame(CollectionDisplayType::GRID, $this->resource()['display_type']);
    }

    #[Test]
    public function itReturnsTheListDisplayTypeWhenTheCollectionIsSetToList(): void
    {
        $this->collection->update(['display_type' => CollectionDisplayType::LIST]);

        $this->assertSame(CollectionDisplayType::LIST, $this->resource()['display_type']);
    }

    #[Test]
    public function itReturnsTheCollectionDetails(): void
    {
        $resource = $this->resource();

        $this->assertSame($this->collection->id, $resource['id']);
        $this->assertSame($this->collection->title, $resource['title']);
        $this->assertSame($this->collection->description, $resource['description']);
        $this->assertSame($this->collection->published, $resource['published']);
    }
}
