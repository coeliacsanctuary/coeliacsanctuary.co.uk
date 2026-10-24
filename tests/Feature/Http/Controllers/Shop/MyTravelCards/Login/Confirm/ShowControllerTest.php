<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\MyTravelCards\Login\Confirm;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected string $email = 'customer@example.com';

    protected function makeRequest(): TestResponse
    {
        return $this
            ->withCookie('my_travel_cards_email', Crypt::encryptString($this->email))
            ->get(route('my-travel-cards.login.confirm.show'));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itRedirectsToTheLoginPageWithoutTheEmailCookie(): void
    {
        $this->get(route('my-travel-cards.login.confirm.show'))
            ->assertRedirect(route('my-travel-cards.login.show'));
    }

    #[Test]
    public function itRendersTheConfirmLoginPage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->component('Shop/MyTravelCards/ConfirmLogin'));
    }

    #[Test]
    public function itPassesTheDecryptedEmailFromTheCookieToThePage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->where('email', $this->email)->etc());
    }

    #[Test]
    public function itSetsThePageTitle(): void
    {
        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page->where('meta.title', 'Confirm - My Travel Cards')->etc()
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
