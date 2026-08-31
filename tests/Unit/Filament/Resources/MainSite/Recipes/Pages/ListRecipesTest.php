<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes\Pages;

use App\Filament\Resources\MainSite\Recipes\Pages\ListRecipes;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListRecipesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheRecipeList(): void
    {
        Livewire::test(ListRecipes::class)->assertOk();
    }

    #[Test]
    public function itOffersAButtonToCreateARecipe(): void
    {
        Livewire::test(ListRecipes::class)->assertActionExists(CreateAction::class);
    }
}
