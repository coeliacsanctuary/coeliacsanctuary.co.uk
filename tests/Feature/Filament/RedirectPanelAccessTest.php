<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Models\Redirect;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RedirectPanelAccessTest extends TestCase
{
    protected Redirect $redirect;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->redirect = $this->create(Redirect::class);
    }

    #[Test]
    #[DataProvider('redirectPages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('redirectPages')]
    public function signedInUsersCanOpenEveryRedirectPage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function redirectPages(): array
    {
        return [
            'the redirect list' => ['index'],
            'the create page' => ['create'],
            'the edit page' => ['edit'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? RedirectResource::getUrl($page)
            : RedirectResource::getUrl($page, ['record' => $this->redirect]);
    }
}
