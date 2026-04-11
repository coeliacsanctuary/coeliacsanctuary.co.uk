<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetCollectionsForCollectionIndexAction;
use App\Models\EatingOut\EateryCollection;
use App\ResourceCollections\EatingOut\EateryCollectionListCollection;
use App\Resources\EatingOut\EateryCollectionCardResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetCollectionsForCollectionIndexActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withEateryCollections(15);
    }

    #[Test]
    public function itReturnsABlogListCollection(): void
    {
        $this->assertInstanceOf(
            EateryCollectionListCollection::class,
            $this->callAction(GetCollectionsForCollectionIndexAction::class),
        );
    }

    #[Test]
    public function itIsAPaginatedCollection(): void
    {
        $collections = $this->callAction(GetCollectionsForCollectionIndexAction::class);

        $this->assertInstanceOf(LengthAwarePaginator::class, $collections->resource);
    }

    #[Test]
    public function itReturns12ItemsPerPageByDefault(): void
    {
        $this->assertCount(12, $this->callAction(GetCollectionsForCollectionIndexAction::class));
    }

    #[Test]
    public function itCanHaveADifferentPageLimitSpecified(): void
    {
        $this->assertCount(5, $this->callAction(GetCollectionsForCollectionIndexAction::class, perPage: 5));
    }

    #[Test]
    public function eachItemInThePageIsAEateryCollectionCardResource(): void
    {
        $resource = $this->callAction(GetCollectionsForCollectionIndexAction::class)->resource->first();

        $this->assertInstanceOf(EateryCollectionCardResource::class, $resource);
    }

    #[Test]
    public function itLoadsTheMediaRelationship(): void
    {
        /** @var EateryCollection $collecton */
        $collecton = $this->callAction(GetCollectionsForCollectionIndexAction::class)->resource->first()->resource;

        $this->assertTrue($collecton->relationLoaded('media'));
    }

    #[Test]
    public function itCanBeFilteredBySearch(): void
    {
        EateryCollection::query()->first()->update(['title' => 'Test Collection Yay']);

        $collection = $this->callAction(GetCollectionsForCollectionIndexAction::class, search: 'test collection');

        $this->assertCount(1, $collection);
    }
}
