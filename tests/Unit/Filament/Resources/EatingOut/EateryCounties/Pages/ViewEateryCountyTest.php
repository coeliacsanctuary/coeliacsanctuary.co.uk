<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCounties\Pages;

use App\Filament\Resources\EatingOut\EateryCounties\Pages\ViewEateryCounty;
use App\Models\EatingOut\EateryCounty;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewEateryCountyTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->county = $this->create(EateryCounty::class, ['county' => 'Cornwall', 'slug' => 'cornwall', 'legacy' => 'Old Cornwall', 'latlng' => '50.5,-4.6']);
    }

    #[Test]
    public function itShowsTheCounty(): void
    {
        $this->viewPage()
            ->assertSchemaStateSet([
                'county' => 'Cornwall',
                'slug' => 'cornwall',
                'country.country' => 'England',
                'latlng' => '50.5,-4.6',
            ], 'infolist')
            ->assertSchemaComponentExists('primary', 'infolist');
    }

    #[Test]
    public function itCanBeEditedButNotDeleted(): void
    {
        $this->viewPage()->assertActionVisible('edit')->assertActionDoesNotExist('delete');
    }

    protected function viewPage(): Testable
    {
        return Livewire::test(ViewEateryCounty::class, ['record' => $this->county->slug]);
    }
}
