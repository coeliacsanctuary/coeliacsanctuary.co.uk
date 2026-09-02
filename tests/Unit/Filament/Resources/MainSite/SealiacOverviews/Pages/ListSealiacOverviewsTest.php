<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\SealiacOverviews\Pages;

use App\Filament\Resources\MainSite\SealiacOverviews\Pages\ListSealiacOverviews;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListSealiacOverviewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheSealiacOverviewList(): void
    {
        Livewire::test(ListSealiacOverviews::class)->assertOk();
    }

    #[Test]
    public function itDoesNotOfferAButtonToCreateASealiacOverview(): void
    {
        Livewire::test(ListSealiacOverviews::class)->assertActionDoesNotExist(CreateAction::class);
    }
}
