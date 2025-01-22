<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\TravelCards;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function itLoadsThePageAndRendersTheInertiaView(): void
    {
        $this->get(route('shop.travel-cards.landing-page'))
            ->assertInertia(fn (Assert $page) => $page->component('Shop/TravelCards'))
            ->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, ['shop']);

        $this->get(route('shop.travel-cards.landing-page'));
    }
}
