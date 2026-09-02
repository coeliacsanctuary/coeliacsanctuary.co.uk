<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\MainSite\SealiacOverviews\SealiacOverviewResource;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SealiacOverviewPanelAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    #[Test]
    public function guestsAreSentToTheLoginPage(): void
    {
        $this->get(SealiacOverviewResource::getUrl('index'))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    public function signedInUsersCanOpenTheSealiacOverviewList(): void
    {
        $this->actingAs($this->create(User::class))
            ->get(SealiacOverviewResource::getUrl('index'))
            ->assertOk();
    }
}
