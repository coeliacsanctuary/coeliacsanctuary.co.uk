<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAreas\Pages;

use App\Filament\Resources\EatingOut\EateryAreas\Pages\ListEateryAreas;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListEateryAreasTest extends TestCase
{
    protected EateryCounty $london;

    protected EateryTown $borough;

    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london', 'country_id' => 1]);
        $this->borough = $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);
        $this->area = $this->create(EateryArea::class, ['area' => 'Holborn', 'slug' => 'holborn', 'latlng' => '51.5,-0.1', 'town_id' => $this->borough->id]);
    }

    #[Test]
    public function itCanCreateAnArea(): void
    {
        Livewire::test(ListEateryAreas::class)->assertOk()->assertActionVisible('create');
    }
}
