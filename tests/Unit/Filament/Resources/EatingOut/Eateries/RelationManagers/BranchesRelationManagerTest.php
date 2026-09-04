<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BranchesRelationManagerTest extends TestCase
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
    public function itIsAvailableForAnEateryInTheFirstCountry(): void
    {
        $this->assertTrue(BranchesRelationManager::canViewForRecord($this->eatery, EditEatery::class));
    }

    #[Test]
    public function itIsNotAvailableForAnEateryInAnotherCountry(): void
    {
        $country = $this->create(EateryCountry::class, ['country' => 'Ireland']);

        $this->eatery->updateQuietly(['country_id' => $country->id]);

        $this->assertFalse(BranchesRelationManager::canViewForRecord($this->eatery, EditEatery::class));
    }

    #[Test]
    public function itShowsTheBranchesForTheEatery(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->branchesPanel()->assertCanSeeTableRecords([$branch]);
    }

    #[Test]
    public function itDoesNotShowBranchesFromAnotherEatery(): void
    {
        $mine = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);
        $theirs = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->create(Eatery::class)->id]);

        $this->branchesPanel()
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    protected function branchesPanel(): Testable
    {
        return Livewire::test(BranchesRelationManager::class, [
            'ownerRecord' => $this->eatery,
            'pageClass' => EditEatery::class,
        ]);
    }
}
