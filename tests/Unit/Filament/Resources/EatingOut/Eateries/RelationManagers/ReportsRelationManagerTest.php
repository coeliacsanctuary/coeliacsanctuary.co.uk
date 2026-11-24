<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\ViewEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\ReportsRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReport;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportsRelationManagerTest extends TestCase
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
        $mine = $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id]);
        $theirs = $this->create(EateryReport::class, ['wheretoeat_id' => $this->create(Eatery::class)->id]);

        Livewire::test(ReportsRelationManager::class, ['ownerRecord' => $this->eatery, 'pageClass' => $page])
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
