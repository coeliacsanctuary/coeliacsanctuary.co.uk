<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Redirects\Pages;

use App\Filament\Resources\MainSite\Redirects\Pages\EditRedirect;
use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Models\Redirect;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Http\Response;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditRedirectTest extends TestCase
{
    protected Redirect $redirect;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));

        $this->redirect = $this->create(Redirect::class, [
            'from' => '/blog/old-post',
            'to' => '/blog/new-post',
            'status' => Response::HTTP_PERMANENTLY_REDIRECT,
        ]);
    }

    #[Test]
    public function itFillsTheFormFromTheRedirect(): void
    {
        $this->editPage()->assertSchemaStateSet([
            'from' => '/blog/old-post',
            'to' => '/blog/new-post',
            'status' => Response::HTTP_PERMANENTLY_REDIRECT,
        ]);
    }

    #[Test]
    public function itUpdatesTheRedirect(): void
    {
        $this->editPage()
            ->fillForm([
                'from' => '/blog/ancient-post',
                'to' => '/blog/current-post',
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->redirect->refresh();

        $this->assertSame('/blog/ancient-post', $this->redirect->from);
        $this->assertSame('/blog/current-post', $this->redirect->to);
    }

    #[Test]
    public function itMakesARedirectTemporary(): void
    {
        $this->editPage()
            ->fillForm(['status' => Response::HTTP_TEMPORARY_REDIRECT])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(Response::HTTP_TEMPORARY_REDIRECT, $this->redirect->refresh()->status);
    }

    #[Test]
    public function itDoesNotTripTheUniqueRuleOnTheRedirectBeingEdited(): void
    {
        $this->editPage()
            ->fillForm(['to' => '/blog/current-post'])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    public function itWontLetYouReuseAnotherRedirectsFromPath(): void
    {
        $this->create(Redirect::class, ['from' => '/blog/taken']);

        $this->editPage()
            ->fillForm(['from' => '/blog/taken'])
            ->call('save')
            ->assertHasFormErrors(['from' => 'unique']);
    }

    #[Test]
    #[DataProvider('storedStatuses')]
    public function itFallsBackToTheNearestStatusTheFormOffers(int $stored, int $shown): void
    {
        $redirect = $this->create(Redirect::class, ['status' => $stored]);

        Livewire::test(EditRedirect::class, ['record' => $redirect->getRouteKey()])
            ->assertSchemaStateSet(['status' => $shown]);
    }

    public static function storedStatuses(): array
    {
        return [
            '301 shows as permanent' => [Response::HTTP_MOVED_PERMANENTLY, Response::HTTP_PERMANENTLY_REDIRECT],
            '302 shows as temporary' => [Response::HTTP_FOUND, Response::HTTP_TEMPORARY_REDIRECT],
            '307 shows as temporary' => [Response::HTTP_TEMPORARY_REDIRECT, Response::HTTP_TEMPORARY_REDIRECT],
            '308 shows as permanent' => [Response::HTTP_PERMANENTLY_REDIRECT, Response::HTTP_PERMANENTLY_REDIRECT],
        ];
    }

    #[Test]
    public function itDoesNotLetYouDeleteARedirect(): void
    {
        $this->editPage()->assertActionDoesNotExist(DeleteAction::class);
    }

    #[Test]
    public function itSendsTheUserBackToTheRedirectListAfterSaving(): void
    {
        $this->editPage()->call('save')->assertRedirect(RedirectResource::getUrl('index'));
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditRedirect::class, ['record' => $this->redirect->getRouteKey()]);
    }
}
