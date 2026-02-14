<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Middleware\LogChatMiddleware;
use App\Ai\Tools\FindRecipeForIngredientsTool;
use App\Ai\Tools\FindTravelCardTool;
use App\Ai\Tools\GetEateryAreasTool;
use App\Ai\Tools\GetEateryCountiesTool;
use App\Ai\Tools\GetEateryCountriesTool;
use App\Ai\Tools\GetEateryTownsTool;
use App\Ai\Tools\GreetingTool;
use App\Ai\Tools\ListAllTravelCardsTool;
use App\Ai\Tools\ListEateriesInTownTool;
use App\Ai\Tools\SearchBlogTagsTool;
use App\Ai\Tools\SearchEateriesBySearchTermTool;
use App\Ai\Tools\SearchRecipesTool;
use App\Ai\Tools\ViewBlogsForBlogTagTool;
use App\Ai\Tools\ViewBlogTool;
use App\Ai\Tools\ViewRecipeTool;
use App\Ai\Tools\WhatAreTravelCardsTool;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class AskSealiac implements Agent, Conversational, HasMiddleware, HasTools
{
    use Promptable;

    /** @var array<array{role: string, message: string}> */
    protected array $messages = [];

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $recipeAllergens = RecipeAllergen::query()->get();
        $recipeMeals = RecipeMeal::query()->get();
        $recipeFeatures = RecipeFeature::query()->get();

        $eateryVenueTypes = EateryVenueType::query()->get();
        $eateryTypes = EateryType::query()->get();
        $eateryFeatures = EateryFeature::query()->get();

        return view('prompts.ask-sealiac-base-instructions', [
            'recipeAllergens' => $recipeAllergens,
            'recipeMeals' => $recipeMeals,
            'recipeFeatures' => $recipeFeatures,
            'eateryVenueTypes' => $eateryVenueTypes,
            'eateryTypes' => $eateryTypes,
            'eateryFeatures' => $eateryFeatures,
        ])->render();
    }

    public function messages(): iterable
    {
        return collect($this->messages)
            ->map(fn (array $message) => new Message($message['role'], $message['message']))
            ->all();
    }

    /** @param array<array{role: string, message: string}> $messages */
    public function withMessages(array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    /** @return Tool[] */
    protected function recipeTools(): array
    {
        return [
            new GreetingTool(),
            new SearchRecipesTool(),
            new FindRecipeForIngredientsTool(),
            new ViewRecipeTool(),
        ];
    }

    /** @return Tool[] */
    public function eateryTools(): array
    {
        return [
            new GetEateryCountriesTool(),
            new GetEateryCountiesTool(),
            new GetEateryTownsTool(),
            new GetEateryAreasTool(),
            new SearchEateriesBySearchTermTool(),
            new ListEateriesInTownTool(),
        ];
    }

    /** @return Tool[] */
    protected function shopTools(): array
    {
        return [
            new FindTravelCardTool(),
            new WhatAreTravelCardsTool(),
            new ListAllTravelCardsTool(),
        ];
    }

    /** @return Tool[] */
    protected function blogTools(): array
    {
        return [
            new SearchBlogTagsTool(),
            new ViewBlogsForBlogTagTool(),
            new ViewBlogTool(),
        ];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            ...$this->recipeTools(),
            ...$this->eateryTools(),
            ...$this->shopTools(),
            ...$this->blogTools(),
        ];
    }

    public function middleware(): array
    {
        return [
            new LogChatMiddleware(),
        ];
    }
}
