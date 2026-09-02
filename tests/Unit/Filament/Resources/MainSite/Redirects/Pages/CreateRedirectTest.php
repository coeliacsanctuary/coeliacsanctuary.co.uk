<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Redirects\Pages;

use App\Filament\Resources\MainSite\Redirects\Pages\CreateRedirect;
use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Models\Redirect;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Response;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itCreatesTheRedirect(): void
    {
        $this->assertDatabaseEmpty(Redirect::class);

        $this->createRedirect()->assertNotified();

        $this->assertDatabaseCount(Redirect::class, 1);

        $redirect = $this->createdRedirect();

        $this->assertSame('/blog/old-post', $redirect->from);
        $this->assertSame('/blog/new-post', $redirect->to);
        $this->assertSame(Response::HTTP_PERMANENTLY_REDIRECT, $redirect->status);
    }

    #[Test]
    public function itCreatesATemporaryRedirect(): void
    {
        $this->createRedirect(['status' => Response::HTTP_TEMPORARY_REDIRECT]);

        $this->assertSame(Response::HTTP_TEMPORARY_REDIRECT, $this->createdRedirect()->status);
    }

    #[Test]
    public function itStartsANewRedirectWithNoHits(): void
    {
        $this->createRedirect();

        $this->assertSame(0, $this->createdRedirect()->hits);
    }

    #[Test]
    public function itSendsTheUserBackToTheRedirectListAfterCreating(): void
    {
        $this->createRedirect()->assertRedirect(RedirectResource::getUrl('index'));
    }

    protected function createRedirect(array $overrides = []): Testable
    {
        return Livewire::test(CreateRedirect::class)
            ->fillForm([
                'from' => '/blog/old-post',
                'to' => '/blog/new-post',
                'status' => Response::HTTP_PERMANENTLY_REDIRECT,
                ...$overrides,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    protected function createdRedirect(): Redirect
    {
        return Redirect::query()->withoutGlobalScopes()->firstOrFail();
    }
}
