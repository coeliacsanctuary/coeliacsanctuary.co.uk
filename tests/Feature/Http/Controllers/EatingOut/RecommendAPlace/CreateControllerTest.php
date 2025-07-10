<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\RecommendAPlace;

use App\Actions\EatingOut\ComputeRecommendAPlaceBackLinkAction;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CreateControllerTest extends TestCase
{
    protected function visitPage(): TestResponse
    {
        return $this->get(route('eating-out.recommend.index'));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->visitPage()->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, ['eatery']);

        $this->visitPage();
    }

    #[Test]
    public function itCallsTheComputeRecommendAPlaceBackLinkActionAction(): void
    {
        $this->expectAction(ComputeRecommendAPlaceBackLinkAction::class);

        $this->visitPage();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitPage()->assertInertia(fn (Assert $page) => $page->component('EatingOut/RecommendAPlace'));
    }
}
