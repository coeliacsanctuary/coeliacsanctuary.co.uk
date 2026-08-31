<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Collections\Pages;

use App\Filament\Resources\MainSite\Collections\CollectionResource;
use App\Filament\Resources\MainSite\Collections\Pages\EditCollection;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditCollectionTest extends TestCase
{
    protected Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->actingAs($this->create(User::class));

        $this->collection = $this->create(Collection::class, [
            'title' => 'Gluten Free Baking',
            'slug' => 'gluten-free-baking',
            'long_description' => 'Everything I bake.',
            'meta_keywords' => 'baking,bread',
            'body' => '<p>A few of my favourites.</p>',
            'live' => true,
        ]);

        $this->collection->addMedia(UploadedFile::fake()->image('header.jpg'))->toMediaCollection('primary');
        $this->collection->addMedia(UploadedFile::fake()->image('social.jpg'))->toMediaCollection('social');
    }

    #[Test]
    public function itFillsTheFormFromTheCollection(): void
    {
        $this->editPage()->assertSchemaStateSet([
            'title' => 'Gluten Free Baking',
            'slug' => 'gluten-free-baking',
            'long_description' => 'Everything I bake.',
            'meta_keywords' => 'baking,bread',
            'meta_description' => $this->collection->meta_description,
            'body' => '<p>A few of my favourites.</p>',
        ]);
    }

    #[Test]
    public function itShowsALiveCollectionAsLive(): void
    {
        $this->editPage()->assertSchemaComponentStateSet('status', 'live');
    }

    #[Test]
    public function itShowsAScheduledCollectionAsScheduled(): void
    {
        $this->collection->update(['live' => false, 'publish_at' => Carbon::now()->addDay()]);

        $this->editPage()->assertSchemaComponentStateSet('status', 'scheduled');
    }

    #[Test]
    public function itFillsTheGroupsAndItemsInOrder(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $blog = $this->create(Blog::class);

        $cakes = $this->build(CollectionGroup::class)->create([
            'collection_id' => $this->collection->id,
            'title' => 'Cakes',
        ]);

        $breads = $this->build(CollectionGroup::class)->create([
            'collection_id' => $this->collection->id,
            'title' => 'Breads',
        ]);

        $cakes->update(['position' => 2]);
        $breads->update(['position' => 1]);

        $this->build(CollectionGroupItem::class)->forBlog($blog)->create([
            'collection_group_id' => $breads->id,
            'position' => 1,
        ]);

        $groups = $this->editPage()->instance()->form->getRawState()['groups'];

        $this->assertCount(2, $groups);
        $this->assertSame(['Breads', 'Cakes'], array_column($groups, 'title'));

        $firstGroup = reset($groups);
        $items = $firstGroup['items'];

        $this->assertCount(1, $items);
        $this->assertSame($blog->id, (int) reset($items)['item_id']);
        $this->assertSame(Blog::class, reset($items)['item_type']);

        $undoRepeaterFake();
    }

    #[Test]
    public function itUpdatesTheCollection(): void
    {
        $this->editPage()
            ->fillForm(['title' => 'Gluten Free Bread'])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->assertSame('Gluten Free Bread', $this->collection->refresh()->title);
    }

    #[Test]
    public function itAddsAGroupToAnExistingCollection(): void
    {
        $blog = $this->create(Blog::class);

        $this->editPage()
            ->fillForm([
                'groups' => [
                    [
                        'title' => 'Breads',
                        'body' => null,
                        'items' => [
                            ['item_type' => Blog::class, 'item_id' => $blog->id],
                        ],
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount(CollectionGroup::class, 1);
        $this->assertDatabaseCount(CollectionGroupItem::class, 1);
        $this->assertSame('Breads', $this->collection->groups()->first()->title);
    }

    #[Test]
    public function itRemovesEveryGroup(): void
    {
        $this->build(CollectionGroup::class)->create(['collection_id' => $this->collection->id]);

        $this->editPage()->fillForm(['groups' => []])->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseEmpty(CollectionGroup::class);
    }

    #[Test]
    public function itUnpublishesACollectionMovedBackToDraft(): void
    {
        $this->collection->update(['publish_at' => Carbon::now()]);

        $this->editPage()->fillForm(['status' => 'draft'])->call('save')->assertHasNoFormErrors();

        $this->collection->refresh();

        $this->assertFalse($this->collection->live);
        $this->assertNull($this->collection->publish_at);
    }

    #[Test]
    public function itSchedulesACollectionMovedToScheduled(): void
    {
        $this->editPage()
            ->fillForm(['status' => 'scheduled', 'publish_at' => '2026-09-05 08:00:00'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->collection->refresh();

        $this->assertFalse($this->collection->live);
        $this->assertSame('2026-09-05 08:00:00', $this->collection->publish_at->toDateTimeString());
    }

    #[Test]
    public function itSendsTheUserBackToTheCollectionListAfterSaving(): void
    {
        $this->editPage()->call('save')->assertRedirect(CollectionResource::getUrl('index'));
    }

    #[Test]
    public function itCanEditACollectionThatIsNotLive(): void
    {
        $collection = $this->create(Collection::class, ['live' => false]);

        Livewire::test(EditCollection::class, ['record' => $collection->getRouteKey()])->assertOk();
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditCollection::class, ['record' => $this->collection->getRouteKey()]);
    }
}
