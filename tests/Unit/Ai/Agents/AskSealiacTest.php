<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\AskSealiac;
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
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class AskSealiacTest extends TestCase
{
    #[Test]
    public function itReturnsInstructionsThatContainTheExpectedContent(): void
    {
        $allergen = $this->create(RecipeAllergen::class);
        $meal = $this->create(RecipeMeal::class);
        $feature = $this->create(RecipeFeature::class);
        $eateryType = $this->create(EateryType::class);
        $venueType = $this->create(EateryVenueType::class);
        $eateryFeature = $this->create(EateryFeature::class);

        $agent = new AskSealiac();
        $instructions = (string) $agent->instructions();

        $this->assertStringContainsString('Sealiac the Seal', $instructions);
        $this->assertStringContainsString($allergen->allergen, $instructions);
        $this->assertStringContainsString($meal->meal, $instructions);
        $this->assertStringContainsString($feature->feature, $instructions);
        $this->assertStringContainsString($eateryType->name, $instructions);
        $this->assertStringContainsString($venueType->venue_type, $instructions);
        $this->assertStringContainsString($eateryFeature->feature, $instructions);
    }

    #[Test]
    public function itReturnsAnEmptyMessageCollectionByDefault(): void
    {
        $agent = new AskSealiac();

        $messages = $agent->messages();

        $this->assertIsArray($messages);
        $this->assertEmpty($messages);
    }

    #[Test]
    public function itReturnsMessagesAsMessageObjects(): void
    {
        $agent = new AskSealiac();

        $agent->withMessages([
            ['role' => 'user', 'message' => 'Hello!'],
            ['role' => 'assistant', 'message' => 'Hi there!'],
        ]);

        $messages = $agent->messages();

        $this->assertCount(2, $messages);
        $this->assertContainsOnlyInstancesOf(Message::class, $messages);
        $this->assertEquals('user', $messages[0]->role->value);
        $this->assertEquals('Hello!', $messages[0]->content);
        $this->assertEquals('assistant', $messages[1]->role->value);
        $this->assertEquals('Hi there!', $messages[1]->content);
    }

    #[Test]
    public function withMessagesReturnsSelfForFluentChaining(): void
    {
        $agent = new AskSealiac();

        $result = $agent->withMessages([]);

        $this->assertSame($agent, $result);
    }

    #[Test]
    public function itReturnsAllExpectedTools(): void
    {
        $agent = new AskSealiac();

        $tools = $agent->tools();

        $expectedTools = [
            GreetingTool::class,
            SearchRecipesTool::class,
            FindRecipeForIngredientsTool::class,
            ViewRecipeTool::class,
            GetEateryCountriesTool::class,
            GetEateryCountiesTool::class,
            GetEateryTownsTool::class,
            GetEateryAreasTool::class,
            SearchEateriesBySearchTermTool::class,
            ListEateriesInTownTool::class,
            FindTravelCardTool::class,
            WhatAreTravelCardsTool::class,
            ListAllTravelCardsTool::class,
            SearchBlogTagsTool::class,
            ViewBlogsForBlogTagTool::class,
            ViewBlogTool::class,
        ];

        $this->assertCount(count($expectedTools), $tools);

        foreach ($tools as $tool) {
            $this->assertInstanceOf(Tool::class, $tool);
        }

        $toolClasses = array_map(fn ($tool) => $tool::class, $tools);

        $this->assertEquals($expectedTools, $toolClasses);
    }

    #[Test]
    public function itReturnsTheRecipeTools(): void
    {
        $agent = new AskSealiac();

        $reflection = new ReflectionMethod($agent, 'recipeTools');
        $tools = $reflection->invoke($agent);

        $this->assertCount(4, $tools);
        $this->assertInstanceOf(GreetingTool::class, $tools[0]);
        $this->assertInstanceOf(SearchRecipesTool::class, $tools[1]);
        $this->assertInstanceOf(FindRecipeForIngredientsTool::class, $tools[2]);
        $this->assertInstanceOf(ViewRecipeTool::class, $tools[3]);
    }

    #[Test]
    public function itReturnsTheEateryTools(): void
    {
        $agent = new AskSealiac();

        $reflection = new ReflectionMethod($agent, 'eateryTools');
        $tools = $reflection->invoke($agent);

        $this->assertCount(6, $tools);
        $this->assertInstanceOf(GetEateryCountriesTool::class, $tools[0]);
        $this->assertInstanceOf(GetEateryCountiesTool::class, $tools[1]);
        $this->assertInstanceOf(GetEateryTownsTool::class, $tools[2]);
        $this->assertInstanceOf(GetEateryAreasTool::class, $tools[3]);
        $this->assertInstanceOf(SearchEateriesBySearchTermTool::class, $tools[4]);
        $this->assertInstanceOf(ListEateriesInTownTool::class, $tools[5]);
    }

    #[Test]
    public function itReturnsTheShopTools(): void
    {
        $agent = new AskSealiac();

        $reflection = new ReflectionMethod($agent, 'shopTools');
        $tools = $reflection->invoke($agent);

        $this->assertCount(3, $tools);
        $this->assertInstanceOf(FindTravelCardTool::class, $tools[0]);
        $this->assertInstanceOf(WhatAreTravelCardsTool::class, $tools[1]);
        $this->assertInstanceOf(ListAllTravelCardsTool::class, $tools[2]);
    }

    #[Test]
    public function itReturnsTheBlogTools(): void
    {
        $agent = new AskSealiac();

        $reflection = new ReflectionMethod($agent, 'blogTools');
        $tools = $reflection->invoke($agent);

        $this->assertCount(3, $tools);
        $this->assertInstanceOf(SearchBlogTagsTool::class, $tools[0]);
        $this->assertInstanceOf(ViewBlogsForBlogTagTool::class, $tools[1]);
        $this->assertInstanceOf(ViewBlogTool::class, $tools[2]);
    }

    #[Test]
    public function itReturnsTheExpectedMiddleware(): void
    {
        $agent = new AskSealiac();

        $middleware = $agent->middleware();

        $this->assertCount(1, $middleware);
        $this->assertInstanceOf(LogChatMiddleware::class, $middleware[0]);
    }
}
