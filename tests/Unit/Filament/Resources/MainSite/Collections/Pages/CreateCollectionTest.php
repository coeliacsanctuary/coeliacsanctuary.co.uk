<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Collections\Pages;

use App\Filament\Resources\MainSite\Collections\Pages\CreateCollection;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itCreatesTheCollection(): void
    {
        $this->assertDatabaseEmpty(Collection::class);

        $this->createCollection()->assertNotified()->assertRedirect();

        $this->assertDatabaseCount(Collection::class, 1);

        $collection = $this->createdCollection();

        $this->assertSame('Gluten Free Baking', $collection->title);
        $this->assertSame('gluten-free-baking', $collection->slug);
        $this->assertSame('Everything I bake.', $collection->long_description);
        $this->assertSame('baking,bread', $collection->meta_keywords);
        $this->assertSame('My gluten free baking collection.', $collection->meta_description);
        $this->assertSame('<p>A few of my favourites.</p>', $collection->body);
    }

    #[Test]
    public function itCreatesTheGroupsAndItemsInOneSave(): void
    {
        $blog = $this->create(Blog::class);
        $recipe = $this->create(Recipe::class);

        $this->assertDatabaseEmpty(CollectionGroup::class);
        $this->assertDatabaseEmpty(CollectionGroupItem::class);

        $this->createCollection([
            'groups' => [
                [
                    'title' => 'Breads',
                    'body' => 'The bread ones.',
                    'items' => [
                        ['item_type' => Blog::class, 'item_id' => $blog->id],
                        ['item_type' => Recipe::class, 'item_id' => $recipe->id],
                    ],
                ],
                [
                    'title' => 'Cakes',
                    'body' => null,
                    'items' => [
                        ['item_type' => Recipe::class, 'item_id' => $recipe->id],
                    ],
                ],
            ],
        ]);

        $collection = $this->createdCollection();
        $groups = $collection->groups()->get();

        $this->assertCount(2, $groups);
        $this->assertSame(['Breads', 'Cakes'], $groups->pluck('title')->all());
        $this->assertSame('The bread ones.', $groups->first()->body);

        $this->assertDatabaseCount(CollectionGroupItem::class, 3);

        $this->assertSame(
            [$blog->id, $recipe->id],
            $groups->first()->items()->pluck('item_id')->all()
        );

        $this->assertSame(
            [Blog::class, Recipe::class],
            $groups->first()->items()->pluck('item_type')->all()
        );

        $this->assertSame([$recipe->id], $groups->last()->items()->pluck('item_id')->all());
    }

    #[Test]
    public function itOrdersTheGroupsAndItemsAsTheyWereEntered(): void
    {
        $recipe = $this->create(Recipe::class);

        $this->createCollection([
            'groups' => [
                [
                    'title' => 'Breads',
                    'items' => [
                        ['item_type' => Recipe::class, 'item_id' => $recipe->id],
                        ['item_type' => Recipe::class, 'item_id' => $recipe->id],
                    ],
                ],
                [
                    'title' => 'Cakes',
                    'items' => [
                        ['item_type' => Recipe::class, 'item_id' => $recipe->id],
                    ],
                ],
            ],
        ]);

        $groups = $this->createdCollection()->groups()->get();

        $this->assertSame([1, 2], $groups->pluck('position')->all());
        $this->assertSame([1, 2], $groups->first()->items()->pluck('position')->all());
        $this->assertSame([1], $groups->last()->items()->pluck('position')->all());
    }

    #[Test]
    public function itStoresTheTitleAndDescriptionOverridesOnAnItem(): void
    {
        $blog = $this->create(Blog::class);

        $this->createCollection([
            'groups' => [
                [
                    'title' => 'Breads',
                    'items' => [
                        [
                            'item_type' => Blog::class,
                            'item_id' => $blog->id,
                            'item_title' => 'My favourite loaf',
                            'item_description' => 'Worth the effort.',
                        ],
                    ],
                ],
            ],
        ]);

        $item = CollectionGroupItem::query()->firstOrFail();

        $this->assertSame('My favourite loaf', $item->item_title);
        $this->assertSame('Worth the effort.', $item->item_description);
    }

    #[Test]
    public function itCreatesACollectionWithNoGroups(): void
    {
        $this->createCollection(['groups' => []]);

        $this->assertDatabaseCount(Collection::class, 1);
        $this->assertDatabaseEmpty(CollectionGroup::class);
    }

    #[Test]
    public function itStoresTheDisplayType(): void
    {
        $this->createCollection(['display_type' => 'list']);

        $this->assertSame('list', $this->createdCollection()->display_type->value);
    }

    #[Test]
    public function itPublishesACollectionSetToLive(): void
    {
        $this->createCollection(['status' => 'live', 'publish_at' => Carbon::now()->addDay()]);

        $collection = $this->createdCollection();

        $this->assertTrue($collection->live);
        $this->assertNull($collection->publish_at);
    }

    #[Test]
    public function itSchedulesACollectionSetToScheduled(): void
    {
        $this->createCollection(['status' => 'scheduled', 'publish_at' => '2026-09-01 09:00:00']);

        $collection = $this->createdCollection();

        $this->assertFalse($collection->live);
        $this->assertSame('2026-09-01 09:00:00', $collection->publish_at->toDateTimeString());
    }

    #[Test]
    public function itLeavesACollectionSetToDraftUnpublished(): void
    {
        $this->createCollection(['status' => 'draft', 'publish_at' => Carbon::now()->addDay()]);

        $collection = $this->createdCollection();

        $this->assertFalse($collection->live);
        $this->assertNull($collection->publish_at);
    }

    #[Test]
    public function itStoresTheHomepageSettings(): void
    {
        $this->createCollection([
            'display_on_homepage' => true,
            'items_to_display' => '6',
            'remove_from_homepage' => '2026-09-15 09:00:00',
        ]);

        $collection = $this->createdCollection();

        $this->assertTrue($collection->display_on_homepage);
        $this->assertSame(6, $collection->items_to_display);
        $this->assertSame('2026-09-15 09:00:00', $collection->remove_from_homepage->toDateTimeString());
    }

    #[Test]
    public function itStoresTheHeaderAndSocialImages(): void
    {
        $this->createCollection();

        $collection = $this->createdCollection();

        $this->assertCount(1, $collection->getMedia('primary'));
        $this->assertCount(1, $collection->getMedia('social'));
    }

    #[Test]
    public function itStoresTheHeaderImageAltText(): void
    {
        $this->createCollection(['header_image_alt_text' => 'A tray of bread rolls']);

        $this->assertSame('A tray of bread rolls', $this->createdCollection()->header_image_alt_text);
    }

    protected function createCollection(array $overrides = []): Testable
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
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    protected function createdCollection(): Collection
    {
        return Collection::query()->withoutGlobalScopes()->firstOrFail();
    }
}
