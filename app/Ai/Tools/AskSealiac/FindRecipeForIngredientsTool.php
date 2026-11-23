<?php

declare(strict_types=1);

namespace App\Ai\Tools\AskSealiac;

use App\Builders\Recipes\RecipeBuilder;
use App\Ai\Concerns\FormatsRecipes;
use App\Models\Recipes\Recipe;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Tools\Request;
use Stringable;

class FindRecipeForIngredientsTool extends BaseTool
{
    use FormatsRecipes;

    public function description(): Stringable|string
    {
        return <<<'Text'
        Search for recipes with ingredients that the user has listed they have at home.

        You can also include one or more optional allergens to filter on. Please note that if you include a filter, you can guarantee that the results are gluten free, and free from the specified allergens.

        You can also filter by meal (breakfast, lunch, dinner etc), or by a special feature (low calorie etc).
        Text;
    }

    protected function execute(Request $request): Stringable|string
    {
        return Recipe::query()
            ->where(
                fn (Builder $builder) => $request
                    ->collect('ingredients')
                    ->each(fn (string $ingredient) => $builder->orWhereLike('ingredients', "%{$ingredient}%"))
            )
            ->with(['media', 'nutrition'])
            ->when($request->filled('allergens'), fn (RecipeBuilder $builder): RecipeBuilder => $builder->hasFreeFrom($request->array('allergens')))
            ->when($request->filled('meals'), fn (RecipeBuilder $builder): RecipeBuilder => $builder->hasMeals($request->array('meals')))
            ->when($request->filled('features'), fn (RecipeBuilder $builder): RecipeBuilder => $builder->hasFeatures($request->array('features')))
            ->get()
            ->map($this->formatRecipe(...))
            ->toJson();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ingredients' => $schema->array()->items($schema->string())->max(10)->required(),
            'allergens' => $schema->array()->items($schema->string())->nullable()->required(),
            'meals' => $schema->array()->items($schema->string())->nullable()->required(),
            'features' => $schema->array()->items($schema->string())->nullable()->required(),
        ];
    }
}
