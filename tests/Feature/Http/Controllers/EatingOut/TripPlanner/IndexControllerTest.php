<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\TripPlanner;

use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function visitPage(): TestResponse
    {
        return $this->get(route('eating-out.trip-planner'));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->visitPage()->assertOk();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page->component('EatingOut/TripPlanner'));
    }
}
