<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Collections\Schemas;

use App\Filament\Forms\Components\CollectionItemSelect;
use App\Filament\Resources\MainSite\Collections\Pages\CreateCollection;
use App\Filament\Resources\MainSite\Collections\Pages\EditCollection;
use App\Models\Collections\Collection;
use App\Models\User;
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
    public function itCollapsesTheGroupsWhenEditing(): void
    {
        $collection = $this->create(Collection::class);

        Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])
            ->assertSchemaComponentExists(
                'groups',
                checkComponentUsing: fn (Repeater $component): bool => $component->isCollapsed(),
            );
    }

    #[Test]
    public function itLeavesTheGroupsExpandedWhenCreating(): void
    {
        Livewire::test(CreateCollection::class)
            ->assertSchemaComponentExists(
                'groups',
                checkComponentUsing: fn (Repeater $component): bool => ! $component->isCollapsed(),
            );
    }

    #[Test]
    public function itPicksTheItemWithTheCollectionItemSelect(): void
    {
        Livewire::test(CreateCollection::class)
            ->fillForm([
                'groups' => [
                    ['title' => 'Breads', 'items' => [['item_type' => null, 'item_id' => null]]],
                ],
            ])
            ->assertSchemaComponentExists(
                'groups.0.items.0.item_id',
                checkComponentUsing: fn ($component): bool => $component instanceof CollectionItemSelect,
            );
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
