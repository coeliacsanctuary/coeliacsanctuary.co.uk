<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Recipes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\Recipes\BuildRecipeIndexTitleAction;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Tests\TestCase;

class BuildRecipeIndexTitleActionTest extends TestCase
{
    #[Test]
    public function itReturnsTheDefaultTitleWithNoFilters(): void
    {
        $this->assertSame('Gluten Free Recipes', $this->callAction(BuildRecipeIndexTitleAction::class, []));
    }

    #[Test]
    public function itReturnsTheDefaultTitleWhenMoreThanOneFilterIsSelected(): void
    {
        $this->create(RecipeFeature::class, ['feature' => 'Vegan', 'slug' => 'vegan']);
        $this->create(RecipeMeal::class, ['meal' => 'Breakfast', 'slug' => 'breakfast']);

        $this->assertSame('Gluten Free Recipes', $this->callAction(
            BuildRecipeIndexTitleAction::class,
            ['features' => ['vegan'], 'meals' => ['breakfast']],
        ));
    }

    #[Test]
    public function itReturnsTheDefaultTitleWhenTwoOfTheSameFilterAreSelected(): void
    {
        $this->create(RecipeMeal::class, ['meal' => 'Breakfast', 'slug' => 'breakfast']);
        $this->create(RecipeMeal::class, ['meal' => 'Lunch', 'slug' => 'lunch']);

        $this->assertSame('Gluten Free Recipes', $this->callAction(
            BuildRecipeIndexTitleAction::class,
            ['meals' => ['breakfast', 'lunch']],
        ));
    }

    #[Test]
    public function itReturnsTheDefaultTitleWhenTheFilterDoesntExist(): void
    {
        $this->assertSame('Gluten Free Recipes', $this->callAction(
            BuildRecipeIndexTitleAction::class,
            ['features' => ['not-a-feature']],
        ));
    }

    public static function allergens(): array
    {
        return [
            'celery' => ['Celery', 'celery', 'Gluten Free and Celery Free Recipes'],
            'crustaceans' => ['Crustaceans', 'crustaceans', 'Gluten Free and Crustacean Free Recipes'],
            'dairy' => ['Dairy', 'dairy', 'Gluten Free and Dairy Free Recipes'],
            'egg' => ['Egg', 'egg', 'Gluten Free and Egg Free Recipes'],
            'fish' => ['Fish', 'fish', 'Gluten Free and Fish Free Recipes'],
            'lupin' => ['Lupin', 'lupin', 'Gluten Free and Lupin Free Recipes'],
            'molluscs' => ['Molluscs', 'molluscs', 'Gluten Free and Mollusc Free Recipes'],
            'mustard' => ['Mustard', 'mustard', 'Gluten Free and Mustard Free Recipes'],
            'peanuts' => ['Peanuts', 'peanuts', 'Gluten Free and Peanut Free Recipes'],
            'sesame' => ['Sesame', 'sesame', 'Gluten Free and Sesame Free Recipes'],
            'soya' => ['Soya', 'soya', 'Gluten Free and Soya Free Recipes'],
            'sulphites' => ['Sulphites', 'sulphites', 'Gluten Free and Sulphite Free Recipes'],
            'tree nuts' => ['Tree Nuts', 'tree-nuts', 'Gluten Free and Tree Nut Free Recipes'],
        ];
    }

    #[Test]
    #[DataProvider('allergens')]
    public function itBuildsTheTitleForASingleAllergen(string $name, string $slug, string $expected): void
    {
        $this->create(RecipeAllergen::class, ['allergen' => $name, 'slug' => $slug]);

        $this->assertSame($expected, $this->callAction(
            BuildRecipeIndexTitleAction::class,
            ['freeFrom' => [$slug]],
        ));
    }

    public static function features(): array
    {
        return [
            'fodmap friendly' => ['FODMAP Friendly', 'fodmap-friendly', 'Gluten Free Low FODMAP Recipes'],
            'healthier option' => ['Healthier Option', 'healthier-option', 'Healthier Gluten Free Recipes'],
            'high fibre' => ['High Fibre', 'high-fibre', 'Gluten Free High Fibre Recipes'],
            'high protein' => ['High Protein', 'high-protein', 'Gluten Free High Protein Recipes'],
            'low calorie' => ['Low Calorie', 'low-calorie', 'Gluten Free Low Calorie Recipes'],
            'low fat' => ['Low Fat', 'low-fat', 'Gluten Free Low Fat Recipes'],
            'low sugar' => ['Low Sugar', 'low-sugar', 'Gluten Free Low Sugar Recipes'],
            'vegan' => ['Vegan', 'vegan', 'Gluten Free Vegan Recipes'],
            'vegetarian' => ['Vegetarian', 'vegetarian', 'Gluten Free Vegetarian Recipes'],
        ];
    }

    #[Test]
    #[DataProvider('features')]
    public function itBuildsTheTitleForASingleFeature(string $name, string $slug, string $expected): void
    {
        $this->create(RecipeFeature::class, ['feature' => $name, 'slug' => $slug]);

        $this->assertSame($expected, $this->callAction(
            BuildRecipeIndexTitleAction::class,
            ['features' => [$slug]],
        ));
    }

    public static function meals(): array
    {
        return [
            'breakfast' => ['Breakfast', 'breakfast', 'Gluten Free Breakfast Recipes'],
            'dessert' => ['Dessert', 'dessert', 'Gluten Free Dessert Recipes'],
            'dinner' => ['Dinner', 'dinner', 'Gluten Free Dinner Recipes'],
            'lunch' => ['Lunch', 'lunch', 'Gluten Free Lunch Recipes'],
            'snacks' => ['Snacks', 'snacks', 'Gluten Free Snack Recipes'],
        ];
    }

    #[Test]
    #[DataProvider('meals')]
    public function itBuildsTheTitleForASingleMeal(string $name, string $slug, string $expected): void
    {
        $this->create(RecipeMeal::class, ['meal' => $name, 'slug' => $slug]);

        $this->assertSame($expected, $this->callAction(
            BuildRecipeIndexTitleAction::class,
            ['meals' => [$slug]],
        ));
    }
}
