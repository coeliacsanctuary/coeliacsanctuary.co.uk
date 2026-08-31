<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Collections\Tables;

use App\Filament\Resources\MainSite\Collections\CollectionResource;
use App\Filament\Resources\MainSite\Collections\Pages\ListCollections;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itShowsEveryCollectionWhateverItsStatus(): void
    {
        $live = $this->create(Collection::class, ['live' => true]);
        $notLive = $this->create(Collection::class, ['live' => false]);

        Livewire::test(ListCollections::class)->assertCanSeeTableRecords([$live, $notLive]);
    }

    #[Test]
    public function itShowsTheNewestCollectionsFirst(): void
    {
        $collections = $this->create(Collection::class, 3);

        Livewire::test(ListCollections::class)
            ->assertCanSeeTableRecords($collections->reverse()->values(), inOrder: true);
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheCollectionColumns(string $column): void
    {
        $this->create(Collection::class);

        Livewire::test(ListCollections::class)->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'id' => ['id'],
            'title' => ['title'],
            'status' => ['status'],
            'display on homepage' => ['display_on_homepage'],
            'remove from homepage' => ['remove_from_homepage'],
            'items to display' => ['items_to_display'],
            'groups count' => ['groups_count'],
            'items count' => ['items_count'],
            'created at' => ['created_at'],
            'updated at' => ['updated_at'],
        ];
    }

    #[Test]
    public function itDoesNotShowThePublishDate(): void
    {
        $this->create(Collection::class);

        Livewire::test(ListCollections::class)->assertTableColumnDoesNotExist('publish_at');
    }

    #[Test]
    public function itLabelsTheIdColumn(): void
    {
        Livewire::test(ListCollections::class)
            ->assertTableColumnExists('id', fn (TextColumn $column): bool => $column->getLabel() === 'ID');
    }

    #[Test]
    public function itCountsTheGroupsAndItemsInACollection(): void
    {
        $blog = $this->create(Blog::class);
        $collection = $this->create(Collection::class);

        $breads = $this->build(CollectionGroup::class)->create(['collection_id' => $collection->id]);
        $this->build(CollectionGroup::class)->create(['collection_id' => $collection->id]);

        $this->build(CollectionGroupItem::class)->forBlog($blog)->create(['collection_group_id' => $breads->id]);
        $this->build(CollectionGroupItem::class)->forBlog($blog)->create(['collection_group_id' => $breads->id]);

        Livewire::test(ListCollections::class)
            ->assertTableColumnStateSet('groups_count', 2, $collection)
            ->assertTableColumnStateSet('items_count', 2, $collection);
    }

    #[Test]
    public function itFindsACollectionByTitle(): void
    {
        $wanted = $this->create(Collection::class, ['title' => 'Gluten Free Baking']);
        $other = $this->create(Collection::class, ['title' => 'Where To Eat In Crewe']);

        Livewire::test(ListCollections::class)
            ->searchTable('Gluten Free Baking')
            ->assertCanSeeTableRecords([$wanted])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itFindsACollectionById(): void
    {
        $collections = $this->create(Collection::class, 2);

        Livewire::test(ListCollections::class)
            ->searchTable((string) $collections->last()->id)
            ->assertCanSeeTableRecords([$collections->last()])
            ->assertCanNotSeeTableRecords([$collections->first()]);
    }

    #[Test]
    public function itFiltersToCollectionsShownOnTheHomepage(): void
    {
        $shown = $this->build(Collection::class)->displayedOnHomepage()->create();
        $hidden = $this->build(Collection::class)->notOnHomepage()->create();

        Livewire::test(ListCollections::class)
            ->filterTable('display_on_homepage', true)
            ->assertCanSeeTableRecords([$shown])
            ->assertCanNotSeeTableRecords([$hidden]);
    }

    #[Test]
    public function itLinksEachRowToTheEditPage(): void
    {
        $collection = $this->create(Collection::class);

        $this->assertSame(
            CollectionResource::getUrl('edit', ['record' => $collection]),
            Livewire::test(ListCollections::class)->instance()->getTable()->getRecordUrl($collection)
        );
    }

    #[Test]
    public function itOffersAViewLinkForALiveCollection(): void
    {
        $collection = $this->create(Collection::class, ['live' => true]);

        Livewire::test(ListCollections::class)->assertActionVisible(TestAction::make('view')->table($collection));
    }

    #[Test]
    public function itHidesTheViewLinkForACollectionThatIsntLive(): void
    {
        $collection = $this->create(Collection::class, ['live' => false]);

        Livewire::test(ListCollections::class)->assertActionHidden(TestAction::make('view')->table($collection));
    }

    #[Test]
    public function theViewLinkOpensTheCollectionOnTheWebsiteInANewTab(): void
    {
        $collection = $this->create(Collection::class, ['live' => true]);

        Livewire::test(ListCollections::class)->assertActionExists(
            TestAction::make('view')->table($collection),
            fn (Action $action): bool => $action->getUrl() === $collection->absolute_link && $action->shouldOpenUrlInNewTab(),
        );
    }

    #[Test]
    public function itOffersAnEditActionForEveryCollection(): void
    {
        $collection = $this->create(Collection::class);

        Livewire::test(ListCollections::class)->assertActionExists(TestAction::make(EditAction::class)->table($collection));
    }
}
