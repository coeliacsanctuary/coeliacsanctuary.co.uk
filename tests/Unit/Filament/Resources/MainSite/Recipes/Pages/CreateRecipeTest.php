<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes\Pages;

use App\Filament\Resources\MainSite\Recipes\Pages\CreateRecipe;
use App\Models\Faqs\Faq;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use App\Models\Recipes\RecipeNutrition;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateRecipeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itCreatesTheRecipe(): void
    {
        $this->assertDatabaseEmpty(Recipe::class);

        $this->createRecipe()->assertNotified()->assertRedirect();

        $this->assertDatabaseCount(Recipe::class, 1);

        $recipe = $this->createdRecipe();

        $this->assertSame('Gluten Free Victoria Sponge', $recipe->title);
        $this->assertSame('gluten-free-victoria-sponge', $recipe->slug);
        $this->assertSame('Victoria Sponge', $recipe->short_title);
        $this->assertSame('A classic gluten free sponge cake.', $recipe->description);
        $this->assertSame('cake,sponge,baking', $recipe->search_tags);
        $this->assertSame('Alison Peters', $recipe->author);
        $this->assertSame('cake,sponge', $recipe->meta_tags);
        $this->assertSame('How to make a gluten free Victoria sponge.', $recipe->meta_description);
        $this->assertSame('20 Minutes', $recipe->prep_time);
        $this->assertSame('25 Minutes', $recipe->cook_time);
        $this->assertSame('8 Slices', $recipe->serving_size);
        $this->assertSame('slice', $recipe->per);
        $this->assertSame('Weigh the flour.', $recipe->ingredients);
        $this->assertSame('Cream the butter and sugar.', $recipe->method);
    }

    #[Test]
    public function itStoresTheNutritionalInformation(): void
    {
        $this->assertDatabaseEmpty(RecipeNutrition::class);

        $this->createRecipe();

        $this->assertDatabaseCount(RecipeNutrition::class, 1);

        $nutrition = $this->createdRecipe()->nutrition;

        $this->assertSame(350, $nutrition->calories);
        $this->assertSame(40, $nutrition->carbs);
        $this->assertSame(18, $nutrition->fat);
        $this->assertSame(5, $nutrition->protein);
        $this->assertSame(2, $nutrition->fibre);
        $this->assertSame(25, $nutrition->sugar);
    }

    #[Test]
    public function itPublishesARecipeSetToLive(): void
    {
        $this->createRecipe(['status' => 'live', 'publish_at' => Carbon::now()->addDay()]);

        $recipe = $this->createdRecipe();

        $this->assertTrue($recipe->live);
        $this->assertNull($recipe->publish_at);
    }

    #[Test]
    public function itSchedulesARecipeSetToScheduled(): void
    {
        $this->createRecipe(['status' => 'scheduled', 'publish_at' => '2026-09-01 09:00:00']);

        $recipe = $this->createdRecipe();

        $this->assertFalse($recipe->live);
        $this->assertSame('2026-09-01 09:00:00', $recipe->publish_at->toDateTimeString());
    }

    #[Test]
    public function itLeavesARecipeSetToDraftUnpublished(): void
    {
        $this->createRecipe(['status' => 'draft', 'publish_at' => Carbon::now()->addDay()]);

        $recipe = $this->createdRecipe();

        $this->assertFalse($recipe->live);
        $this->assertNull($recipe->publish_at);
    }

    #[Test]
    public function itStoresTheHeaderSquareAndSocialImages(): void
    {
        $this->createRecipe();

        $recipe = $this->createdRecipe();

        $this->assertCount(1, $recipe->getMedia('primary'));
        $this->assertCount(1, $recipe->getMedia('square'));
        $this->assertCount(1, $recipe->getMedia('social'));
        $this->assertStringContainsString('header', $recipe->getMedia('primary')->first()->file_name);
        $this->assertStringContainsString('square', $recipe->getMedia('square')->first()->file_name);
        $this->assertStringContainsString('social', $recipe->getMedia('social')->first()->file_name);
    }

    #[Test]
    public function itStoresTheHeaderImageAltText(): void
    {
        $this->createRecipe(['header_image_alt_text' => 'A Victoria sponge on a cake stand']);

        $this->assertSame('A Victoria sponge on a cake stand', $this->createdRecipe()->header_image_alt_text);
    }

    #[Test]
    public function itMarksANewRecipeAsFreeFromEveryAllergenThatIsntTicked(): void
    {
        $dairy = $this->create(RecipeAllergen::class, ['allergen' => 'Dairy']);
        $egg = $this->create(RecipeAllergen::class, ['allergen' => 'Egg']);

        $this->createRecipe(['allergens' => [$egg->id]]);

        $this->assertSame([$dairy->id], $this->createdRecipe()->allergens()->pluck('recipe_allergens.id')->all());
    }

    #[Test]
    public function itAttachesTheMealsAndFeatures(): void
    {
        $breakfast = $this->create(RecipeMeal::class, ['meal' => 'Breakfast']);
        $this->create(RecipeMeal::class, ['meal' => 'Dinner']);
        $vegan = $this->create(RecipeFeature::class, ['feature' => 'Vegan']);
        $this->create(RecipeFeature::class, ['feature' => 'High Protein']);

        $this->createRecipe(['meals' => [$breakfast->id], 'features' => [$vegan->id]]);

        $recipe = $this->createdRecipe();

        $this->assertSame([$breakfast->id], $recipe->meals()->pluck('recipe_meals.id')->all());
        $this->assertSame([$vegan->id], $recipe->features()->pluck('recipe_features.id')->all());
    }

    #[Test]
    public function itAttachesTheRelatedRecipes(): void
    {
        $related = $this->create(Recipe::class);

        $this->createRecipe(['relatedRecipes' => [$related->id]]);

        $recipe = Recipe::query()->withoutGlobalScopes()->whereKeyNot($related->id)->firstOrFail();

        $this->assertSame([$related->id], $recipe->relatedRecipes()->pluck('recipes.id')->all());
    }

    #[Test]
    public function itStoresTheFaqsInOrder(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $this->assertDatabaseEmpty(Faq::class);

        $this->createRecipe([
            'faqs' => [
                ['question' => 'Can I make it dairy free?', 'answer' => 'Yes.'],
                ['question' => 'Can I freeze it?', 'answer' => 'Also yes.'],
            ],
        ]);

        $this->assertDatabaseCount(Faq::class, 2);

        $faqs = $this->createdRecipe()->faqs()->get();

        $this->assertSame('Can I make it dairy free?', $faqs->first()->question);
        $this->assertSame('Yes.', $faqs->first()->answer);
        $this->assertSame('Can I freeze it?', $faqs->last()->question);
        $this->assertSame(1, $faqs->first()->position);
        $this->assertSame(2, $faqs->last()->position);

        $undoRepeaterFake();
    }

    protected function createRecipe(array $overrides = []): Testable
    {
        return Livewire::test(CreateRecipe::class)
            ->fillForm($this->validFormData($overrides))
            ->call('create')
            ->assertHasNoFormErrors();
    }

    protected function validFormData(array $overrides = []): array
    {
        return [
            'title' => 'Gluten Free Victoria Sponge',
            'slug' => 'gluten-free-victoria-sponge',
            'short_title' => 'Victoria Sponge',
            'description' => 'A classic gluten free sponge cake.',
            'search_tags' => 'cake,sponge,baking',
            'author' => 'Alison Peters',
            'meta_tags' => 'cake,sponge',
            'meta_description' => 'How to make a gluten free Victoria sponge.',
            'body' => '<p>This is my favourite bake.</p>',
            'ingredients' => 'Weigh the flour.',
            'method' => 'Cream the butter and sugar.',
            'prep_time' => '20 Minutes',
            'cook_time' => '25 Minutes',
            'serving_size' => '8 Slices',
            'per' => 'slice',
            'status' => 'live',
            'faqs' => [],
            'allergens' => [],
            'meals' => [],
            'features' => [],
            'relatedRecipes' => [],
            'nutrition' => [
                'calories' => 350,
                'carbs' => 40,
                'fat' => 18,
                'protein' => 5,
                'fibre' => 2,
                'sugar' => 25,
            ],
            'header' => [UploadedFile::fake()->image('header.jpg')],
            'square' => [UploadedFile::fake()->image('square.jpg')],
            'social' => [UploadedFile::fake()->image('social.jpg')],
            ...$overrides,
        ];
    }

    protected function createdRecipe(): Recipe
    {
        return Recipe::query()->withoutGlobalScopes()->firstOrFail();
    }
}
