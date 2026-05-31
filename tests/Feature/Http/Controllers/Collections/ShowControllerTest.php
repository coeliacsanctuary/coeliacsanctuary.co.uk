<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Collections;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\Recipes\Recipe;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCollections(1);

        $this->collection = Collection::query()->first();
    }

    #[Test]
    public function itReturnsNotFoundForARecipeThatDoesntExist(): void
    {
        $this->get(route('collection.show', ['collection' => 'foobar']))->assertNotFound();
    }

    protected function visitCollection(): TestResponse
    {
        return $this->get(route('collection.show', ['collection' => $this->collection]));
    }

    #[Test]
    public function itReturnsNotFoundForACollectionThatIsntLive(): void
    {
        $this->collection->update(['live' => false]);

        $this->visitCollection()->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForACollectionThatIsLive(): void
    {
        $this->visitCollection()->assertOk();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitCollection()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Collection/Show')
                    ->has('collection')
                    ->where('collection.title', 'Collection 0')
                    ->etc()
            );
    }

    #[Test]
    public function itDoesNotIncludeCollectionItemsWhoseRelatedItemIsNotLive(): void
    {
        $group = CollectionGroup::query()->first();

        $liveRecipe = $this->create(Recipe::class, ['live' => true]);
        $liveRecipe->addMedia(UploadedFile::fake()->image('recipe.jpg'))->toMediaCollection('primary');
        $liveRecipe->addMedia(UploadedFile::fake()->image('recipe.jpg'))->toMediaCollection('social');

        $deadRecipe = $this->create(Recipe::class, ['live' => false]);

        $this->build(CollectionGroupItem::class)->forRecipe($liveRecipe)->create(['collection_group_id' => $group->id]);
        $this->build(CollectionGroupItem::class)->forRecipe($deadRecipe)->create(['collection_group_id' => $group->id]);

        $this->visitCollection()
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Collection/Show')
                    ->has('collection.groups.0.items', 1)
                    ->where('collection.groups.0.items.0.title', $liveRecipe->title)
            );
    }
}
