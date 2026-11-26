<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAlerts\Actions;

use App\Filament\Resources\EatingOut\EateryAlerts\Pages\ListEateryAlerts;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;
use App\Models\EatingOut\EateryCheck;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IgnoreAlertForSixMonthsActionTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itIsEnabledForAPendingAlert(): void
    {
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->create();

        Livewire::test(ListEateryAlerts::class)->assertActionEnabled(TestAction::make('ignoreForSixMonths')->table($alert));
    }

    #[Test]
    public function itIsDisabledForACompletedAlert(): void
    {
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->completed()->create();

        Livewire::test(ListEateryAlerts::class)->assertActionDisabled(TestAction::make('ignoreForSixMonths')->table($alert));
    }

    #[Test]
    public function itIsDisabledForAnIgnoredAlert(): void
    {
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->ignored()->create();

        Livewire::test(ListEateryAlerts::class)->assertActionDisabled(TestAction::make('ignoreForSixMonths')->table($alert));
    }

    #[Test]
    public function itConfirmsBeforeRunning(): void
    {
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->create();

        Livewire::test(ListEateryAlerts::class)->assertActionExists(
            TestAction::make('ignoreForSixMonths')->table($alert),
            fn (Action $action): bool => $action->isConfirmationRequired(),
        );
    }

    #[Test]
    public function itIgnoresTheAlert(): void
    {
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->create();

        $this->ignoreForSixMonths($alert);

        $this->assertTrue($alert->refresh()->ignored);
    }

    #[Test]
    public function itDisablesTheWebsiteCheckForSixMonths(): void
    {
        $this->freezeSecond();
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->create();

        $this->ignoreForSixMonths($alert);

        $this->assertDatabaseCount(EateryCheck::class, 1);
        $this->assertTrue($this->eatery->check()->first()->website_check_disabled_until->equalTo(now()->addMonths(6)));
    }

    #[Test]
    public function itUpdatesAnExistingCheckRatherThanAddingAnother(): void
    {
        $this->freezeSecond();
        $this->build(EateryCheck::class)->forEatery($this->eatery)->websiteCheckDisabledUntil(now()->subDay())->create();
        $alert = $this->build(EateryAlert::class)->on($this->eatery)->create();

        $this->ignoreForSixMonths($alert);

        $this->assertDatabaseCount(EateryCheck::class, 1);
        $this->assertTrue($this->eatery->check()->first()->website_check_disabled_until->equalTo(now()->addMonths(6)));
    }

    protected function ignoreForSixMonths(EateryAlert $alert): void
    {
        Livewire::test(ListEateryAlerts::class)
            ->callAction(TestAction::make('ignoreForSixMonths')->table($alert))
            ->assertNotified();
    }
}
