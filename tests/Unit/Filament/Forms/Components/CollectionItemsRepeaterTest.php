<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Forms\Components;

use App\Filament\Forms\Components\CollectionItemsRepeater;
use App\Filament\Resources\MainSite\Collections\Pages\EditCollection;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsFilamentSchemas;
use Tests\TestCase;

class CollectionItemsRepeaterTest extends TestCase
{
    use BuildsFilamentSchemas;

    protected Collection $collection;

    protected CollectionGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));

        $this->collection = $this->create(Collection::class);
        $this->group = $this->create(CollectionGroup::class, ['collection_id' => $this->collection->id]);
    }

    #[Test]
    public function itStartsWithNoItems(): void
    {
        $this->assertSame([], $this->mountedComponent('items', [CollectionItemsRepeater::make('items')])->getTiles());

        $this->editPage()->assertSee('No items in this group yet.');
    }

    #[Test]
    public function itOnlyAddsAnItemOnceTheModalIsSaved(): void
    {
        $blog = $this->create(Blog::class);

        $page = $this->editPage()->mountAction($this->action('add'));

        $this->assertCount(0, $this->rows($page));

        $page->fillForm(['item_type' => Blog::class])
            ->fillForm(['item_id' => $blog->id])
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $rows = $this->rows($page);

        $this->assertCount(1, $rows);
        $this->assertSame(Blog::class, reset($rows)['item_type']);
        $this->assertSame($blog->id, (int) reset($rows)['item_id']);
    }

    #[Test]
    public function itRequiresATypeAndAnItem(): void
    {
        $page = $this->editPage()
            ->callAction($this->action('add'), ['item_type' => null])
            ->assertHasFormErrors(['item_type' => 'required']);

        $page->fillForm(['item_type' => Blog::class])
            ->callMountedAction()
            ->assertHasFormErrors(['item_id' => 'required']);

        $this->assertCount(0, $this->rows($page));
    }

    #[Test]
    public function itShowsTheFieldsAsTheyBecomeRelevant(): void
    {
        $blog = $this->create(Blog::class);

        $this->editPage()
            ->mountAction($this->action('add'))
            ->assertSchemaComponentHidden('item_id')
            ->assertSchemaComponentHidden('item_title')
            ->fillForm(['item_type' => Blog::class])
            ->assertSchemaComponentVisible('item_id')
            ->assertSchemaComponentHidden('item_title')
            ->fillForm(['item_id' => $blog->id])
            ->assertSchemaComponentVisible('item_title')
            ->assertSchemaComponentVisible('item_description');
    }

    #[Test]
    public function changingTheTypeClearsTheItem(): void
    {
        $item = $this->addBlog();

        $this->editPage()
            ->mountAction($this->action('edit', $item))
            ->assertSchemaStateSet(['item_id' => $item->item_id])
            ->fillForm(['item_type' => Recipe::class])
            ->assertSchemaStateSet(['item_id' => null]);
    }

    #[Test]
    public function itLeavesTheItemAloneUntilAnEditIsSaved(): void
    {
        $item = $this->addBlog(['item_title' => 'Original']);

        $page = $this->editPage()
            ->mountAction($this->action('edit', $item))
            ->fillForm(['item_title' => 'Changed']);

        $this->assertSame('Original', $this->rows($page)["record-{$item->id}"]['item_title']);

        $page->callMountedAction();

        $this->assertSame('Changed', $this->rows($page)["record-{$item->id}"]['item_title']);
    }

    #[Test]
    public function itAsksForConfirmationBeforeRemovingAnItem(): void
    {
        $item = $this->addBlog();
        $other = $this->addBlog();

        $page = $this->editPage()->mountAction($this->action('delete', $item));

        $this->assertCount(2, $this->rows($page));

        $page->callMountedAction();

        $this->assertSame(["record-{$other->id}"], array_keys($this->rows($page)));
    }

    #[Test]
    public function itReordersTheItems(): void
    {
        $first = $this->addBlog();
        $second = $this->addBlog();

        $page = $this->editPage()->callAction(
            TestAction::make('reorder')
                ->schemaComponent($this->componentKey())
                ->arguments(['items' => ["record-{$second->id}", "record-{$first->id}"]])
        );

        $this->assertSame(["record-{$second->id}", "record-{$first->id}"], array_keys($this->rows($page)));
    }

    #[Test]
    public function itShowsTheItemsOwnDetailsOnTheTile(): void
    {
        $blog = $this->create(Blog::class, ['title' => 'Gluten Free Bread', 'meta_description' => 'A lovely loaf']);
        $blog->addMedia(UploadedFile::fake()->image('blog.jpg'))->toMediaCollection('primary');

        $tile = $this->tile(['item_type' => Blog::class, 'item_id' => $blog->id]);

        $this->assertSame('Gluten Free Bread', $tile['title']);
        $this->assertSame('A lovely loaf', $tile['meta']);
        $this->assertSame($blog->getFirstMediaUrl('primary'), $tile['imageUrl']);
    }

    #[Test]
    public function itPrefersTheOverridesOnTheTile(): void
    {
        $tile = $this->tile([
            'item_type' => Blog::class,
            'item_id' => $this->create(Blog::class)->id,
            'item_title' => 'My favourite loaf',
            'item_description' => 'Worth the effort.',
        ]);

        $this->assertSame('My favourite loaf', $tile['title']);
        $this->assertSame('Worth the effort.', $tile['meta']);
    }

    #[Test]
    public function itShowsTheAddressOfAnEateryAndABranch(): void
    {
        $eatery = $this->create(Eatery::class, ['address' => '12 Market Street']);
        $branch = $this->build(NationwideBranch::class)->forEatery($eatery)->create(['address' => '1 High Street']);

        $this->assertSame('12 Market Street', $this->tile(['item_type' => Eatery::class, 'item_id' => $eatery->id])['meta']);
        $this->assertSame('1 High Street', $this->tile(['item_type' => NationwideBranch::class, 'item_id' => $branch->id])['meta']);
    }

    #[Test]
    public function itShowsAnItemThatIsNotLive(): void
    {
        $blog = $this->build(Blog::class)->notLive()->create(['title' => 'Draft Blog']);

        $this->assertSame('Draft Blog', $this->tile(['item_type' => Blog::class, 'item_id' => $blog->id])['title']);
    }

    #[Test]
    public function itCopesWithAnItemThatNoLongerExists(): void
    {
        $tile = $this->tile(['item_type' => Blog::class, 'item_id' => 999]);

        $this->assertSame('No item selected', $tile['title']);
        $this->assertNull($tile['meta']);
        $this->assertNull($tile['imageUrl']);
    }

    #[Test]
    public function itUsesAnIconForEachType(): void
    {
        $icons = array_column($this->tiles([
            ['item_type' => Blog::class],
            ['item_type' => Recipe::class],
            ['item_type' => Eatery::class],
            ['item_type' => NationwideBranch::class],
            ['item_type' => null],
        ]), 'icon');

        $this->assertSame([
            Heroicon::OutlinedBookOpen,
            Heroicon::OutlinedCake,
            Heroicon::OutlinedBuildingStorefront,
            Heroicon::OutlinedBuildingStorefront,
            Heroicon::OutlinedPhoto,
        ], $icons);
    }

    #[Test]
    public function itDoesNotRunMoreQueriesForMoreItemsOfTheSameType(): void
    {
        $rows = $this->build(Blog::class)->count(5)->create()
            ->map(fn (Blog $blog): array => ['item_type' => Blog::class, 'item_id' => $blog->id])
            ->all();

        $this->assertSame($this->queriesForTiles([$rows[0]]), $this->queriesForTiles($rows));
    }

    /** @param array<int, array<string, mixed>> $rows */
    protected function queriesForTiles(array $rows): int
    {
        $component = $this->repeater($rows);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $component->getTiles();

        DB::disableQueryLog();

        $count = count(DB::getQueryLog());

        $this->assertGreaterThan(0, $count);

        return $count;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function tile(array $row): array
    {
        return $this->tiles([$row])[0];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function tiles(array $rows): array
    {
        return $this->repeater($rows)->getTiles();
    }

    /** @param array<int, array<string, mixed>> $rows */
    protected function repeater(array $rows): CollectionItemsRepeater
    {
        return $this->mountedComponent('items', [CollectionItemsRepeater::make('items')->default($rows)]);
    }

    /** @param array<string, mixed> $attributes */
    protected function addBlog(array $attributes = []): CollectionGroupItem
    {
        return $this->build(CollectionGroupItem::class)
            ->forBlog($this->create(Blog::class))
            ->create(['collection_group_id' => $this->group->id, ...$attributes]);
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditCollection::class, ['record' => $this->collection->getRouteKey()]);
    }

    protected function action(string $name, ?CollectionGroupItem $item = null): TestAction
    {
        return TestAction::make($name)
            ->schemaComponent($this->componentKey())
            ->arguments($item instanceof CollectionGroupItem ? ['item' => "record-{$item->id}"] : []);
    }

    protected function componentKey(): string
    {
        return "groups.record-{$this->group->id}.items";
    }

    /** @return array<string, array<string, mixed>> */
    protected function rows(Testable $page): array
    {
        return $page->get("data.{$this->componentKey()}");
    }
}
