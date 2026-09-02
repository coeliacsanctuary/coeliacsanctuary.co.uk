<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Redirects\Schemas;

use App\Filament\Resources\MainSite\Redirects\Pages\CreateRedirect;
use App\Models\Redirect;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Response;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedirectFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itAcceptsACompleteRedirect(): void
    {
        $this->fillCreateForm()->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    #[DataProvider('invalidRedirects')]
    public function itValidatesTheRedirect(array $overrides, array $errors): void
    {
        $this->fillCreateForm($overrides)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    }

    public static function invalidRedirects(): array
    {
        return [
            '`from` is required' => [['from' => null], ['from' => 'required']],
            '`to` is required' => [['to' => null], ['to' => 'required']],
            '`status` is required' => [['status' => null], ['status' => 'required']],
        ];
    }

    #[Test]
    public function itWontLetTwoRedirectsShareTheSameFromPath(): void
    {
        $this->create(Redirect::class, ['from' => '/blog/old-post']);

        $this->fillCreateForm(['from' => '/blog/old-post'])
            ->call('create')
            ->assertHasFormErrors(['from' => 'unique']);
    }

    #[Test]
    public function itDefaultsANewRedirectToPermanent(): void
    {
        Livewire::test(CreateRedirect::class)
            ->assertSchemaStateSet(['status' => Response::HTTP_PERMANENTLY_REDIRECT]);
    }

    #[Test]
    public function itDoesNotLetYouEditTheHitCount(): void
    {
        Livewire::test(CreateRedirect::class)->assertSchemaComponentDoesNotExist('hits');
    }

    protected function fillCreateForm(array $overrides = []): Testable
    {
        return Livewire::test(CreateRedirect::class)->fillForm([
            'from' => '/blog/old-post',
            'to' => '/blog/new-post',
            'status' => Response::HTTP_PERMANENTLY_REDIRECT,
            ...$overrides,
        ]);
    }
}
