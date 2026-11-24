<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\Pages\ViewEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewEateryTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class, [
            'name' => 'The Cafe',
            'address' => '<b>1 High Street</b>',
            'info' => 'Lovely cakes',
        ]);
    }

    #[Test]
    public function itShowsTheEatery(): void
    {
        $this->viewEatery()
            ->assertSee('The Cafe')
            ->assertSee('Lovely cakes')
            ->assertSee($this->eatery->website)
            ->assertSee('Crewe')
            ->assertSee('Last Updated');
    }

    #[Test]
    public function itEscapesTheAddress(): void
    {
        $this->viewEatery()->assertDontSeeHtml('<b>1 High Street</b>');
    }

    #[Test]
    public function itShowsTheRestaurantsForAnAttraction(): void
    {
        $this->eatery->update(['type_id' => 2]);

        $this->create(EateryAttractionRestaurant::class, [
            'wheretoeat_id' => $this->eatery->id,
            'restaurant_name' => 'The Grill',
        ]);

        $this->viewEatery()->assertSee('The Grill');
    }

    #[Test]
    public function itLinksToTheEditPage(): void
    {
        $this->viewEatery()->assertActionVisible('edit');
    }

    protected function viewEatery(): Testable
    {
        return Livewire::test(ViewEatery::class, ['record' => $this->eatery->id]);
    }
}
