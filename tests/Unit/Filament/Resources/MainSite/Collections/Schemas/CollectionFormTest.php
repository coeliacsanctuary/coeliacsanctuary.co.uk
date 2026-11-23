<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Collections\Schemas;

use App\Filament\Resources\MainSite\Collections\Pages\CreateCollection;
use App\Filament\Resources\MainSite\Collections\Pages\EditCollection;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CollectionFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itAcceptsACompleteCollection(): void
    {
        $this->fillCreateForm()->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    #[DataProvider('invalidCollections')]
    public function itValidatesTheCollection(array $overrides, array $errors): void
    {
        $this->fillCreateForm($overrides)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    }

    public static function invalidCollections(): array
    {
        return [
            '`title` is required' => [['title' => null], ['title' => 'required']],
            '`title` is max 200 characters' => [['title' => Str::random(201)], ['title' => 'max']],
            '`slug` is required' => [['slug' => null], ['slug' => 'required']],
            '`slug` is max 200 characters' => [['slug' => Str::repeat('a', 201)], ['slug' => 'max']],
            '`slug` rejects capitals' => [['slug' => 'Gluten-Free'], ['slug' => 'regex']],
            '`slug` rejects spaces' => [['slug' => 'gluten free'], ['slug' => 'regex']],
            '`slug` rejects underscores' => [['slug' => 'gluten_free'], ['slug' => 'regex']],
            '`long_description` is required' => [['long_description' => null], ['long_description' => 'required']],
            '`meta_keywords` is required' => [['meta_keywords' => null], ['meta_keywords' => 'required']],
            '`meta_description` is required' => [['meta_description' => null], ['meta_description' => 'required']],
            '`body` is required' => [['body' => null], ['body' => 'required']],
            '`header` is required' => [['header' => []], ['header' => 'required']],
            '`social` is required' => [['social' => []], ['social' => 'required']],
        ];
    }

    #[Test]
    public function itRejectsASlugThatIsAlreadyTaken(): void
    {
        $this->create(Collection::class, ['slug' => 'gluten-free-baking']);

        $this->fillCreateForm(['slug' => 'gluten-free-baking'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itLocksTheSlugWhenEditing(): void
    {
        $collection = $this->create(Collection::class);

        Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
            ->assertSchemaComponentExists(
                'slug',
                checkComponentUsing: fn (TextInput $component): bool => $component->isDisabled(),
            );
    }

    #[Test]
    public function itWritesTheMetaTagsToTheMetaKeywordsColumn(): void
    {
        $this->fillCreateForm(['meta_keywords' => 'baking,bread'])->call('create')->assertHasNoFormErrors();

        $this->assertSame('baking,bread', Collection::query()->withoutGlobalScopes()->firstOrFail()->meta_keywords);
    }

    #[Test]
    public function itOffersTheGridAndListDisplayTypes(): void
    {
        $this->assertSame(
            ['grid' => 'Grid', 'list' => 'List'],
            $this->formComponent('display_type')->getOptions()
        );
    }

    #[Test]
    public function itDefaultsToTheGridDisplayType(): void
    {
        Livewire::test(CreateCollection::class)->assertSchemaComponentStateSet('display_type', 'grid');
    }

    #[Test]
    public function itShowsTheHomepageSettingsForALiveCollection(): void
    {
        Livewire::test(CreateCollection::class)
            ->fillForm(['status' => 'live'])
            ->assertSchemaComponentVisible('display_on_homepage');
    }

    #[Test]
    public function itHidesTheHomepageSettingsForACollectionThatIsntLive(): void
    {
        Livewire::test(CreateCollection::class)
            ->fillForm(['status' => 'draft'])
            ->assertSchemaComponentHidden('display_on_homepage');
    }

    #[Test]
    public function itHidesTheHomepageDetailUntilTheCollectionIsSetToShowOnTheHomepage(): void
    {
        Livewire::test(CreateCollection::class)
            ->fillForm(['status' => 'live', 'display_on_homepage' => false])
            ->assertSchemaComponentHidden('items_to_display')
            ->assertSchemaComponentHidden('remove_from_homepage');
    }

    #[Test]
    public function itShowsTheHomepageDetailOnceTheCollectionIsSetToShowOnTheHomepage(): void
    {
        Livewire::test(CreateCollection::class)
            ->fillForm(['status' => 'live', 'display_on_homepage' => true])
            ->assertSchemaComponentVisible('items_to_display')
            ->assertSchemaComponentVisible('remove_from_homepage');
    }

    #[Test]
    public function itCollapsesTheGroupsWhenThereIsMoreThanOne(): void
    {
        $this->assertTrue($this->groupsRepeater($this->collectionWithGroups(2))->isCollapsed());
    }

    #[Test]
    public function itLeavesASingleGroupExpanded(): void
    {
        $this->assertFalse($this->groupsRepeater($this->collectionWithGroups(1))->isCollapsed());
    }

    #[Test]
    #[DataProvider('groupLabels')]
    public function itLabelsAGroupWithItsTitleAndItemCount(?string $title, int $items, string $label): void
    {
        $collection = $this->create(Collection::class);
        $group = $this->create(CollectionGroup::class, ['collection_id' => $collection->id, 'title' => $title]);
        $blog = $this->create(Blog::class);

        if ($items > 0) {
            $this->build(CollectionGroupItem::class)->forBlog($blog)->count($items)->create(['collection_group_id' => $group->id]);
        }

        $this->assertSame($label, $this->groupsRepeater($collection)->getItemLabel("record-{$group->id}"));
    }

    public static function groupLabels(): array
    {
        return [
            'one item' => ['Breads', 1, 'Breads (1 item)'],
            'several items' => ['Breads', 2, 'Breads (2 items)'],
            'no items' => ['Breads', 0, 'Breads (0 items)'],
            'no title' => [null, 0, 'Untitled group (0 items)'],
        ];
    }

    #[Test]
    public function itAsksForConfirmationBeforeRemovingAGroup(): void
    {
        $collection = $this->collectionWithGroups(1);
        $group = $collection->groups()->firstOrFail();

        $page = Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
            ->mountAction(TestAction::make('delete')->schemaComponent('groups')->arguments(['item' => "record-{$group->id}"]));

        $this->assertCount(1, $page->get('data.groups'));

        $page->callMountedAction();

        $this->assertCount(0, $page->get('data.groups'));
    }

    protected function collectionWithGroups(int $count): Collection
    {
        $collection = $this->create(Collection::class);

        $this->build(CollectionGroup::class)->count($count)->create(['collection_id' => $collection->id]);

        return $collection;
    }

    protected function groupsRepeater(Collection $collection): Repeater
    {
        return Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
            ->instance()
            ->form
            ->getFlatComponents(withHidden: true)['groups'];
    }

    protected function formComponent(string $key): Select
    {
        return Livewire::test(CreateCollection::class)
            ->instance()
            ->form
            ->getFlatComponents(withHidden: true)[$key];
    }

    protected function fillCreateForm(array $overrides = []): Testable
    {
        return Livewire::test(CreateCollection::class)
            ->fillForm([
                'title' => 'Gluten Free Baking',
                'slug' => 'gluten-free-baking',
                'long_description' => 'Everything I bake.',
                'meta_keywords' => 'baking,bread',
                'meta_description' => 'My gluten free baking collection.',
                'body' => '<p>A few of my favourites.</p>',
                'display_type' => 'grid',
                'status' => 'live',
                'groups' => [],
                'header' => [UploadedFile::fake()->image('header.jpg')],
                'social' => [UploadedFile::fake()->image('social.jpg')],
                ...$overrides,
            ]);
    }
}
