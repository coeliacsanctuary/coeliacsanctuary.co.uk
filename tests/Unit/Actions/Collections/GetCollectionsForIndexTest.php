<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Collections;

use App\Actions\Collections\GetCollectionsForIndexAction;
use App\Models\Collections\Collection;
use App\ResourceCollections\Collections\CollectionListCollection;
use App\Resources\Collections\CollectionDetailCardViewResource;
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
        /** @var Collection $collection */
        $collection = $this->callAction(GetCollectionsForIndexAction::class)->resource->first()->resource;

        $this->assertTrue($collection->relationLoaded('media'));
    }

    #[Test]
    public function itHasAnItemsCount(): void
    {
        /** @var Collection $collection */
        $collection = $this->callAction(GetCollectionsForIndexAction::class)->resource->first()->resource;

        $this->assertArrayHasKey('items_count', $collection->attributesToArray());
    }
}
