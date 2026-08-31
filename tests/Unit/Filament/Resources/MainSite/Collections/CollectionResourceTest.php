<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Collections;

use App\Filament\Resources\MainSite\Collections\CollectionResource;
use App\Models\Collections\Collection;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itListsTheNewestCollectionsFirst(): void
    {
        $collections = $this->create(Collection::class, 3);

        $this->assertSame(
            $collections->reverse()->pluck('id')->all(),
            CollectionResource::getEloquentQuery()->pluck('id')->all()
        );
    }

    #[Test]
    public function itIncludesCollectionsThatArentLive(): void
    {
        $this->create(Collection::class, ['live' => true]);
        $this->create(Collection::class, ['live' => false, 'publish_at' => Carbon::now()->addDay()]);
        $this->create(Collection::class, ['live' => false, 'publish_at' => null]);

        $this->assertCount(3, CollectionResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itTransformsTheStatusBeforeSaving(): void
    {
        $data = CollectionResource::mutateForSave([
            'status' => 'Live',
            'publish_at' => Carbon::now(),
            'title' => 'Gluten Free Baking',
        ]);

        $this->assertTrue($data['live']);
        $this->assertNull($data['publish_at']);
        $this->assertArrayNotHasKey('status', $data);
        $this->assertSame('Gluten Free Baking', $data['title']);
    }

    #[Test]
    public function itManagesItsGroupsOnTheFormRatherThanARelationManager(): void
    {
        $this->assertSame([], CollectionResource::getRelations());
    }

    #[Test]
    public function itIsGloballySearchableByTitleAndSlug(): void
    {
        $this->assertSame(['title', 'slug'], CollectionResource::getGloballySearchableAttributes());
    }

    #[Test]
    public function itTitlesRecordsByTheirCollectionTitle(): void
    {
        $this->assertSame('title', CollectionResource::getRecordTitleAttribute());
    }

    #[Test]
    public function itRegistersTheListCreateAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(CollectionResource::getPages()));
    }
}
