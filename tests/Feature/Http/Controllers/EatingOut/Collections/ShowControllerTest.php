<?php

declare(strict_types=1);

namespace Feature\Http\Controllers\EatingOut\Collections;

use App\Models\EatingOut\EateryCollection;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected EateryCollection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withEateryCollections(1);

        $this->collection = EateryCollection::query()->first();
    }

    #[Test]
    public function itReturnsNotFoundForABlogThatDoesntExist(): void
    {
        $this->get(route('eating-out.collections.show', ['eateryCollection' => 'foobar']))->assertNotFound();
    }

    protected function visitEateryCollection(): TestResponse
    {
        return $this->get(route('eating-out.collections.show', ['eateryCollection' => $this->collection]));
    }

    #[Test]
    public function itReturnsNotFoundForACollectionThatIsntLive(): void
    {
        $this->collection->update(['live' => false]);

        $this->visitEateryCollection()->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForACollectionThatIsLive(): void
    {
        $this->visitEateryCollection()->assertOk();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitEateryCollection()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Collections/Show')
                    ->has('collection')
                    ->where('collection.title', 'Eatery Collection 0')
                    ->etc()
            );
    }
}
