<?php

declare(strict_types=1);

namespace Feature\Http\Controllers\EatingOut\Collections;

use App\Actions\EatingOut\GetCollectionsForCollectionIndexAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withEateryCollections(30);
    }

    #[Test]
    public function itLoadsTheEatingOutCollectionsListPage(): void
    {
        $this->get(route('eating-out.collections.index'))->assertOk();
    }

    #[Test]
    public function itCallsTheGetEateryCollectionsForIndexAction(): void
    {
        $this->expectAction(GetCollectionsForCollectionIndexAction::class)
            ->get(route('eating-out.collections.index'));
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, ['eating-out-collection']);

        $this->get(route('eating-out.collections.index'));
    }

    #[Test]
    public function itReturnsTheFirst12Collections(): void
    {
        $this->get(route('eating-out.collections.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Collections/Index')
                    ->has('collections')
                    ->has(
                        'collections.data',
                        12,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'description', 'date', 'image', 'link', 'description'])
                    )
                    ->where('collections.data.0.title', 'Eatery Collection 0')
                    ->where('collections.data.1.title', 'Eatery Collection 1')
                    ->has('collections.links')
                    ->has('collections.meta')
                    ->where('collections.meta.current_page', 1)
                    ->where('collections.meta.per_page', 12)
                    ->where('collections.meta.total', 30)
                    ->etc()
            );
    }

    #[Test]
    public function itCanLoadOtherPages(): void
    {
        $this->get(route('eating-out.collections.index', ['page' => 2]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Collections/Index')
                    ->has('collections')
                    ->has(
                        'collections.data',
                        12,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'description', 'date', 'image', 'link', 'description'])
                    )
                    ->where('collections.data.0.title', 'Eatery Collection 12')
                    ->where('collections.data.1.title', 'Eatery Collection 13')
                    ->has('collections.links')
                    ->has('collections.meta')
                    ->where('collections.meta.current_page', 2)
                    ->where('collections.meta.per_page', 12)
                    ->where('collections.meta.total', 30)
                    ->etc()
            );
    }
}
