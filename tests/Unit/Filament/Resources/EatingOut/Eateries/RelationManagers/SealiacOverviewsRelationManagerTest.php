<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\ViewEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\SealiacOverviewsRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\SealiacOverview;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SealiacOverviewsRelationManagerTest extends TestCase
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
    #[DataProvider('pages')]
    public function itShowsTheRecordsForTheEatery(string $page): void
    {
        $mine = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();
        $theirs = $this->build(SealiacOverview::class)->forEatery($this->create(Eatery::class))->create();

        Livewire::test(SealiacOverviewsRelationManager::class, ['ownerRecord' => $this->eatery, 'pageClass' => $page])
            ->assertOk()
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    public static function pages(): array
    {
        return [
            'view' => [ViewEatery::class],
            'edit' => [EditEatery::class],
        ];
    }
}
