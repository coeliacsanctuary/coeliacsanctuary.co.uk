<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\GetCountyListAction;
use App\Actions\EatingOut\GetMostRatedPlacesAction;
use App\Actions\EatingOut\GetTopRatedPlacesAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Models\EatingOut\EateryCounty;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EateryScaffoldingSeeder::class);
    }

    protected function visitPage(): TestResponse
    {
        return $this->get(route('eating-out.index'));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->visitPage()->assertOk();
    }

    #[Test]
    public function itCallsTheGetCountyListAction(): void
    {
        $this->expectAction(GetCountyListAction::class);

        $this->visitPage();
    }

    #[Test]
    public function itCallsTheTopRatedPlacesAction(): void
    {
        $this->expectAction(GetTopRatedPlacesAction::class);

        $this->visitPage();
    }

    #[Test]
    public function itCallsTheMostRatedPlacesAction(): void
    {
        $this->expectAction(GetMostRatedPlacesAction::class);

        $this->visitPage();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, [
            static fn (string $route, mixed $alterUrl = null) => $route === 'eatery',
        ]);

        $this->visitPage();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitPage()->assertInertia(fn (Assert $page) => $page->component('EatingOut/Index'));
    }
}
