<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\WorkWithUs;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function itLoadsThePageAndRendersTheInertiaView(): void
    {
        $this->get(route('work-with-us'))
            ->assertInertia(fn (Assert $page): Assert => $page->component('WorkWithUs'))
            ->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class);

        $this->get(route('work-with-us'));
    }
}
