<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Redirects\Pages;

use App\Filament\Resources\MainSite\Redirects\Pages\ListRedirects;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListRedirectsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheRedirectList(): void
    {
        Livewire::test(ListRedirects::class)->assertOk();
    }

    #[Test]
    public function itOffersAButtonToCreateARedirect(): void
    {
        Livewire::test(ListRedirects::class)->assertActionExists(CreateAction::class);
    }
}
