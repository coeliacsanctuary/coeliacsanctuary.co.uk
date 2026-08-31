<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipePanelAccessTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->recipe = $this->create(Recipe::class);
    }

    #[Test]
    #[DataProvider('recipePages')]
    public function guestsAreSentToTheLoginPage(string $page): void
    {
        $this->get($this->url($page))->assertRedirect(Filament::getLoginUrl());
    }

    #[Test]
    #[DataProvider('recipePages')]
    public function signedInUsersCanOpenEveryRecipePage(string $page): void
    {
        $this->actingAs($this->create(User::class))
            ->get($this->url($page))
            ->assertOk();
    }

    public static function recipePages(): array
    {
        return [
            'the recipe list' => ['index'],
            'the create page' => ['create'],
            'the edit page' => ['edit'],
            'the metrics page' => ['metrics'],
        ];
    }

    protected function url(string $page): string
    {
        return in_array($page, ['index', 'create'], true)
            ? RecipeResource::getUrl($page)
            : RecipeResource::getUrl($page, ['record' => $this->recipe]);
    }
}
