<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards\Login;

use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected function makeRequest(): TestResponse
    {
        return $this->get(route('my-travel-cards.login'));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itRendersTheLoginPage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->component('Shop/MyTravelCards/Login'));
    }

    #[Test]
    public function itSetsThePageTitle(): void
    {
        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page->where('meta.title', 'Login - My Travel Cards')->etc()
        );
    }

    #[Test]
    public function itDoesntTrackThePage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->where('meta.doNotTrack', true)->etc());
    }

    #[Test]
    public function itDoesntRenderAnyMetaTagsOrDescription(): void
    {
        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->where('meta.tags', [])
                ->where('meta.description', '')
                ->etc()
        );
    }
}
