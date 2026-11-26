<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryMagicRoutes\Pages;

use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\ListEateryMagicRoutes;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListEateryMagicRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itCanCreateAMagicRoute(): void
    {
        Livewire::test(ListEateryMagicRoutes::class)->assertOk()->assertActionVisible('create');
    }
}
