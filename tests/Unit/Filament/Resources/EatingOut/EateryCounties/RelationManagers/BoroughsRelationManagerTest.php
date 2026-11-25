<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCounties\RelationManagers;

use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use App\Filament\Resources\EatingOut\EateryCounties\Pages\ViewEateryCounty;
use App\Filament\Resources\EatingOut\EateryCounties\RelationManagers\BoroughsRelationManager;
use App\Filament\Resources\EatingOut\EateryCounties\RelationManagers\TownsRelationManager;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BoroughsRelationManagerTest extends TestCase
{
    protected EateryCounty $london;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);
    }

    #[Test]
    public function itIsOnlyShownForLondon(): void
    {
        Livewire::test(ViewEateryCounty::class, ['record' => 'london'])
            ->assertSeeLivewire(BoroughsRelationManager::class)
            ->assertDontSeeLivewire(TownsRelationManager::class);
    }

    #[Test]
    public function itCallsThemBoroughs(): void
    {
        $table = $this->boroughsPanel()->instance()->getTable();

        $this->assertSame('Boroughs', $table->getHeading());
        $this->assertSame('Borough', $table->getColumn('town')->getLabel());
        $this->assertSame('borough', $table->getModelLabel());
    }

    #[Test]
    public function itLinksToTheBoroughResource(): void
    {
        $borough = $this->create(EateryTown::class, ['county_id' => $this->london->id]);

        $this->boroughsPanel()
            ->assertCanSeeTableRecords([$borough])
            ->assertActionHasUrl(TestAction::make('create')->table(), EateryBoroughResource::getUrl('create'))
            ->assertActionHasUrl(TestAction::make('edit')->table($borough), EateryBoroughResource::getUrl('edit', ['record' => $borough]));
    }

    protected function boroughsPanel(): Testable
    {
        return Livewire::test(BoroughsRelationManager::class, [
            'ownerRecord' => $this->london,
            'pageClass' => ViewEateryCounty::class,
        ]);
    }
}
