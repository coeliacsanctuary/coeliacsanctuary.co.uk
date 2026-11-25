<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCuisines\Tables;

use App\Filament\Resources\EatingOut\EateryCuisines\EateryCuisineResource;
use App\Filament\Resources\EatingOut\EateryCuisines\Pages\ListEateryCuisines;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCuisine;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCuisinesTableTest extends TestCase
{
    protected EateryCuisine $cuisine;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->cuisine = $this->create(EateryCuisine::class, ['cuisine' => 'Peruvian']);
    }

    #[Test]
    public function itListsTheColumnsInOrder(): void
    {
        $this->assertSame(
            ['cuisine', 'eateries_count'],
            array_keys($this->listCuisines()->instance()->getTable()->getColumns()),
        );
    }

    #[Test]
    public function itSortsByNameByDefault(): void
    {
        $zambian = $this->create(EateryCuisine::class, ['cuisine' => 'Zambian']);
        $ethiopian = $this->create(EateryCuisine::class, ['cuisine' => 'Ethiopian']);

        EateryCuisine::query()
            ->whereKeyNot([$this->cuisine->id, $zambian->id, $ethiopian->id])
            ->delete();

        $this->listCuisines()->assertCanSeeTableRecords([$ethiopian, $this->cuisine, $zambian], inOrder: true);
    }

    #[Test]
    public function itSearchesByName(): void
    {
        $ethiopian = $this->create(EateryCuisine::class, ['cuisine' => 'Ethiopian']);

        $this->listCuisines()
            ->searchTable('Ethiop')
            ->assertCanSeeTableRecords([$ethiopian])
            ->assertCanNotSeeTableRecords([$this->cuisine]);
    }

    #[Test]
    public function itOnlyCountsLiveEateries(): void
    {
        $this->create(Eatery::class, 2, ['cuisine_id' => $this->cuisine->id, 'live' => true]);
        $this->build(Eatery::class)->notLive()->create(['cuisine_id' => $this->cuisine->id]);

        $this->listCuisines()->assertTableColumnStateSet('eateries_count', 2, $this->cuisine);
    }

    #[Test]
    public function itSortsByTheEateriesCount(): void
    {
        $zambian = $this->create(EateryCuisine::class, ['cuisine' => 'Zambian']);

        $this->create(Eatery::class, 2, ['cuisine_id' => $zambian->id]);
        $this->create(Eatery::class, ['cuisine_id' => $this->cuisine->id]);

        $this->listCuisines()
            ->sortTable('eateries_count', 'desc')
            ->assertCanSeeTableRecords([$zambian, $this->cuisine], inOrder: true);
    }

    #[Test]
    public function itOpensTheEditPageWhenARowIsClicked(): void
    {
        $this->assertSame(
            EateryCuisineResource::getUrl('edit', ['record' => $this->cuisine]),
            $this->listCuisines()->instance()->getTable()->getRecordUrl($this->cuisine),
        );
    }

    #[Test]
    public function itCanOnlyBeEdited(): void
    {
        $this->listCuisines()
            ->assertActionVisible(TestAction::make('edit')->table($this->cuisine))
            ->assertActionDoesNotExist(TestAction::make('view')->table($this->cuisine))
            ->assertActionDoesNotExist(TestAction::make('replicate')->table($this->cuisine))
            ->assertActionDoesNotExist(TestAction::make('delete')->table($this->cuisine));
    }

    protected function listCuisines(): Testable
    {
        return Livewire::test(ListEateryCuisines::class);
    }
}
