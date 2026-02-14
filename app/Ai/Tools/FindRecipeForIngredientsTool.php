<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Concerns\FormatsRecipes;
use App\Models\Recipes\Recipe;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Tools\Request;
use Stringable;

class FindRecipeForIngredientsTool extends BaseTool
{
    use FormatsRecipes;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<'Text'
        Search for recipes with ingredients that the user has listed they have at home.

        You can also include one or more optional allergens to filter on. Please note that if you include a filter, you can guarantee that the results are gluten free, and free from the specified allergens.

        You can also filter by meal (breakfast, lunch, dinner etc), or by a special feature (low calorie etc).
        Text;
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        return Recipe::query()
            ->where(
                fn (Builder $builder) => $request /** @phpstan-ignore-line */
                    ->collect('ingredients')
                    ->each(fn (string $ingredient) => $builder->orWhereLike('ingredients', "%{$ingredient}%"))
            )
            ->with(['media', 'nutrition'])
            ->when($request->filled('allergens'), fn (Builder $builder) => $builder->hasFreeFrom($request->array('allergens')))
            ->when($request->filled('meals'), fn (Builder $builder) => $builder->hasMeals($request->array('meals')))
            ->when($request->filled('features'), fn (Builder $builder) => $builder->hasFeatures($request->array('allergens')))
            ->get()
            ->map($this->formatRecipe(...))
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ingredients' => $schema->array()->items($schema->string())->max(10)->required(),
            'allergens' => $schema->array()->items($schema->string())->nullable(),
            'meals' => $schema->array()->items($schema->string())->nullable(),
            'features' => $schema->array()->items($schema->string())->nullable(),
        ];
    }
}
