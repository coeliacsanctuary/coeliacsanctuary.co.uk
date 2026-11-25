<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryBoroughs\Pages;

use App\Filament\Resources\EatingOut\EateryBoroughs\Pages\ListEateryBoroughs;
use App\Models\EatingOut\EateryCounty;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListEateryBoroughsTest extends TestCase
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
    public function itCanCreateABorough(): void
    {
        Livewire::test(ListEateryBoroughs::class)->assertOk()->assertActionVisible('create');
    }
}
