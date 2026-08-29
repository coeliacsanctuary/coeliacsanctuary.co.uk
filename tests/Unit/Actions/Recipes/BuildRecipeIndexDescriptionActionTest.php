<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Recipes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\Recipes\BuildRecipeIndexDescriptionAction;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Tests\TestCase;

class BuildRecipeIndexDescriptionActionTest extends TestCase
{
    protected string $unfiltered = 'Gluten free recipes from Coeliac Sanctuary — tried and tested coeliac friendly bakes, dinners, breakfasts and puddings, all using simple supermarket ingredients.';

    #[Test]
    public function itReturnsTheDefaultDescriptionWithNoFilters(): void
    {
        $this->assertSame($this->unfiltered, $this->callAction(BuildRecipeIndexDescriptionAction::class, []));
    }

    #[Test]
    public function itReturnsTheDefaultDescriptionWhenMoreThanOneFilterIsSelected(): void
    {
        $this->create(RecipeFeature::class, ['feature' => 'Vegan', 'slug' => 'vegan']);
        $this->create(RecipeMeal::class, ['meal' => 'Breakfast', 'slug' => 'breakfast']);

        $this->assertSame($this->unfiltered, $this->callAction(
            BuildRecipeIndexDescriptionAction::class,
            ['features' => ['vegan'], 'meals' => ['breakfast']],
        ));
    }

    #[Test]
    public function itReturnsTheDefaultDescriptionWhenTheFilterDoesntExist(): void
    {
        $this->assertSame($this->unfiltered, $this->callAction(
            BuildRecipeIndexDescriptionAction::class,
            ['meals' => ['not-a-meal']],
        ));
    }

    #[Test]
    public function itBuildsTheFullDescriptionForAnAllergen(): void
    {
        $this->create(RecipeAllergen::class, ['allergen' => 'Dairy', 'slug' => 'dairy']);

        $this->assertSame(
            'Gluten free and dairy free recipes from Coeliac Sanctuary — tried and tested bakes, dinners and puddings, all using simple supermarket ingredients.',
            $this->callAction(BuildRecipeIndexDescriptionAction::class, ['freeFrom' => ['dairy']]),
        );
    }

    #[Test]
    public function itBuildsTheFullDescriptionForAFeature(): void
    {
        $this->create(RecipeFeature::class, ['feature' => 'Vegan', 'slug' => 'vegan']);

        $this->assertSame(
            'Gluten free vegan recipes from Coeliac Sanctuary — tried and tested coeliac friendly bakes, dinners, breakfasts and puddings, all using simple supermarket ingredients.',
            $this->callAction(BuildRecipeIndexDescriptionAction::class, ['features' => ['vegan']]),
        );
    }

    #[Test]
    public function itBuildsTheFullDescriptionForAMeal(): void
    {
        $this->create(RecipeMeal::class, ['meal' => 'Breakfast', 'slug' => 'breakfast']);

        $this->assertSame(
            'Gluten free breakfast recipes from Coeliac Sanctuary — tried and tested coeliac friendly breakfast ideas, all using simple supermarket ingredients.',
            $this->callAction(BuildRecipeIndexDescriptionAction::class, ['meals' => ['breakfast']]),
        );
    }

    public static function allergens(): array
    {
        return [
            'celery' => ['Celery', 'celery', 'Gluten free and celery free recipes'],
            'crustaceans' => ['Crustaceans', 'crustaceans', 'Gluten free and crustacean free recipes'],
            'dairy' => ['Dairy', 'dairy', 'Gluten free and dairy free recipes'],
            'egg' => ['Egg', 'egg', 'Gluten free and egg free recipes'],
            'fish' => ['Fish', 'fish', 'Gluten free and fish free recipes'],
            'lupin' => ['Lupin', 'lupin', 'Gluten free and lupin free recipes'],
            'molluscs' => ['Molluscs', 'molluscs', 'Gluten free and mollusc free recipes'],
            'mustard' => ['Mustard', 'mustard', 'Gluten free and mustard free recipes'],
            'peanuts' => ['Peanuts', 'peanuts', 'Gluten free and peanut free recipes'],
            'sesame' => ['Sesame', 'sesame', 'Gluten free and sesame free recipes'],
            'soya' => ['Soya', 'soya', 'Gluten free and soya free recipes'],
            'sulphites' => ['Sulphites', 'sulphites', 'Gluten free and sulphite free recipes'],
            'tree nuts' => ['Tree Nuts', 'tree-nuts', 'Gluten free and tree nut free recipes'],
        ];
    }

    #[Test]
    #[DataProvider('allergens')]
    public function itOpensTheDescriptionWithTheAllergen(string $name, string $slug, string $expected): void
    {
        $this->create(RecipeAllergen::class, ['allergen' => $name, 'slug' => $slug]);

        $this->assertStringStartsWith($expected . ' from Coeliac Sanctuary', $this->callAction(
            BuildRecipeIndexDescriptionAction::class,
            ['freeFrom' => [$slug]],
        ));
    }

    public static function features(): array
    {
        return [
            'fodmap friendly' => ['FODMAP Friendly', 'fodmap-friendly', 'Gluten free low FODMAP recipes'],
            'healthier option' => ['Healthier Option', 'healthier-option', 'Healthier gluten free recipes'],
            'high fibre' => ['High Fibre', 'high-fibre', 'Gluten free high fibre recipes'],
            'high protein' => ['High Protein', 'high-protein', 'Gluten free high protein recipes'],
            'low calorie' => ['Low Calorie', 'low-calorie', 'Gluten free low calorie recipes'],
            'low fat' => ['Low Fat', 'low-fat', 'Gluten free low fat recipes'],
            'low sugar' => ['Low Sugar', 'low-sugar', 'Gluten free low sugar recipes'],
            'vegan' => ['Vegan', 'vegan', 'Gluten free vegan recipes'],
            'vegetarian' => ['Vegetarian', 'vegetarian', 'Gluten free vegetarian recipes'],
        ];
    }

    #[Test]
    #[DataProvider('features')]
    public function itOpensTheDescriptionWithTheFeature(string $name, string $slug, string $expected): void
    {
        $this->create(RecipeFeature::class, ['feature' => $name, 'slug' => $slug]);

        $this->assertStringStartsWith($expected . ' from Coeliac Sanctuary', $this->callAction(
            BuildRecipeIndexDescriptionAction::class,
            ['features' => [$slug]],
        ));
    }

    public static function meals(): array
    {
        return [
            'breakfast' => ['Breakfast', 'breakfast', 'breakfast'],
            'dessert' => ['Dessert', 'dessert', 'dessert'],
            'dinner' => ['Dinner', 'dinner', 'dinner'],
            'lunch' => ['Lunch', 'lunch', 'lunch'],
            'snacks' => ['Snacks', 'snacks', 'snack'],
        ];
    }

    #[Test]
    #[DataProvider('meals')]
    public function itUsesTheMealInBothHalvesOfTheDescription(string $name, string $slug, string $expected): void
    {
        $this->create(RecipeMeal::class, ['meal' => $name, 'slug' => $slug]);

        $this->assertSame(
            "Gluten free {$expected} recipes from Coeliac Sanctuary — tried and tested coeliac friendly {$expected} ideas, all using simple supermarket ingredients.",
            $this->callAction(BuildRecipeIndexDescriptionAction::class, ['meals' => [$slug]]),
        );
    }
}
