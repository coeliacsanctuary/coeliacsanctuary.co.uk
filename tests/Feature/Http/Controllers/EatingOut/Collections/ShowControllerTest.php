<?php

declare(strict_types=1);

namespace Feature\Http\Controllers\EatingOut\Collections;

use App\Models\EatingOut\EateryCollection;
use App\Pipelines\EatingOut\GetEateries\GetEateriesFromCollectionPipeline;
use App\Services\EatingOut\Filters\GetFiltersForCollection;
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

    #[Test]
    public function itCallsTheGetFiltersForCollection(): void
    {
        $this->mock(GetFiltersForCollection::class)
            ->shouldReceive('setCollection')
            ->once()
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('handle')
            ->once()
            ->andReturn([]);

        $this->visitEateryCollection()
            ->assertInertia(fn (Assert $page) => $page
                ->loadDeferredProps(fn (Assert $reload) => $reload->has('filters'))
            );
    }

    #[Test]
    public function itCallsTheGetEateriesPipeline(): void
    {
        $this->expectPipelineToRun(GetEateriesFromCollectionPipeline::class, collect()->paginate());

        $this->visitEateryCollection()
            ->assertInertia(fn (Assert $page) => $page
                ->loadDeferredProps(fn (Assert $reload) => $reload->has('eateries'))
            );
    }
}
