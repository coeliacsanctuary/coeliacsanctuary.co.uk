<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\RefreshAdsTxt;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RefreshAdsTxtPanelAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    #[Test]
    public function guestsAreSentToTheLoginPage(): void
    {
        $this->get(RefreshAdsTxt::getUrl())->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    public function signedInUsersCanOpenTheRefreshAdsTxtPage(): void
    {
        $this->actingAs($this->create(User::class))
            ->get(RefreshAdsTxt::getUrl())
            ->assertOk();
    }
}
