<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes\Pages;

use App\Filament\Resources\MainSite\Recipes\Pages\EditRecipe;
use App\Filament\Resources\MainSite\Recipes\RecipeResource;
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

class EditRecipeTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->travelTo(Carbon::parse('2026-08-30 12:00:00'));

        $this->actingAs($this->create(User::class));

        $this->recipe = $this->create(Recipe::class, [
            'title' => 'Gluten Free Victoria Sponge',
            'slug' => 'gluten-free-victoria-sponge',
            'short_title' => 'Victoria Sponge',
            'ingredients' => 'Weigh the flour.',
            'method' => 'Cream the butter and sugar.',
            'live' => true,
        ]);

        $this->create(RecipeNutrition::class, ['recipe_id' => $this->recipe->id]);

        $this->addImages($this->recipe);
    }

    #[Test]
    public function itFillsTheFormFromTheRecipe(): void
    {
        $this->editPage()->assertSchemaStateSet([
            'title' => 'Gluten Free Victoria Sponge',
            'slug' => 'gluten-free-victoria-sponge',
            'short_title' => 'Victoria Sponge',
            'ingredients' => 'Weigh the flour.',
            'method' => 'Cream the butter and sugar.',
            'description' => $this->recipe->description,
            'search_tags' => $this->recipe->search_tags,
            'author' => $this->recipe->author,
            'meta_tags' => $this->recipe->meta_tags,
            'meta_description' => $this->recipe->meta_description,
        ]);
    }

    #[Test]
    public function itFillsTheNutritionalInformationFromTheRecipe(): void
    {
        $this->recipe->nutrition->update(['calories' => 350, 'sugar' => 25]);

        $this->editPage()->assertSchemaComponentStateSet('nutrition.calories', 350)
            ->assertSchemaComponentStateSet('nutrition.sugar', 25);
    }

    #[Test]
    public function itUpdatesTheRecipe(): void
    {
        $this->editPage()
            ->fillForm(['title' => 'Gluten Free Chocolate Cake'])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->assertSame('Gluten Free Chocolate Cake', $this->recipe->refresh()->title);
    }

    #[Test]
    public function itUpdatesTheNutritionalInformation(): void
    {
        $this->editPage()
            ->fillForm(['nutrition' => ['calories' => 420, 'carbs' => 50, 'fat' => 20, 'protein' => 6, 'fibre' => 3, 'sugar' => 30]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(420, $this->recipe->refresh()->nutrition->calories);
    }

    #[Test]
    public function theSlugCannotBeChangedWhenEditing(): void
    {
        $this->editPage()->assertSchemaComponentExists(
            'slug',
            checkComponentUsing: fn ($field): bool => $field->isDisabled(),
        );
    }

    #[Test]
    public function itTicksTheAllergensTheRecipeContains(): void
    {
        [$dairy, $egg, $soya] = $this->allergens();

        $this->recipe->allergens()->attach($dairy->id);

        $this->assertSame([$egg->id, $soya->id], $this->tickedAllergens());
    }

    #[Test]
    public function itTicksNothingWhenTheRecipeIsFreeFromEverything(): void
    {
        $this->recipe->allergens()->attach($this->allergens()->pluck('id'));

        $this->assertSame([], $this->tickedAllergens());
    }

    #[Test]
    public function tickingEveryAllergenLeavesTheRecipeFreeFromNothing(): void
    {
        $allergens = $this->allergens();

        $this->recipe->allergens()->attach($allergens->first()->id);

        $this->editPage()
            ->fillForm(['allergens' => $allergens->pluck('id')->all()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([], $this->freeFromAllergens());
    }

    #[Test]
    public function untickingAnAllergenMarksTheRecipeFreeFromIt(): void
    {
        [$dairy, $egg, $soya] = $this->allergens();

        $this->editPage()
            ->fillForm(['allergens' => [$egg->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$dairy->id, $soya->id], $this->freeFromAllergens());
    }

    #[Test]
    public function tickingAnAllergenRemovesItFromTheFreeFromList(): void
    {
        [$dairy, $egg, $soya] = $this->allergens();

        $this->recipe->allergens()->attach([$dairy->id, $egg->id]);

        $this->editPage()
            ->fillForm(['allergens' => [$soya->id, $dairy->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$egg->id], $this->freeFromAllergens());
    }

    #[Test]
    public function itAddsAndRemovesMeals(): void
    {
        $breakfast = $this->create(RecipeMeal::class, ['meal' => 'Breakfast']);
        $dinner = $this->create(RecipeMeal::class, ['meal' => 'Dinner']);

        $this->recipe->meals()->attach($breakfast->id);

        $this->editPage()->fillForm(['meals' => [$dinner->id]])->call('save')->assertHasNoFormErrors();

        $this->assertSame([$dinner->id], $this->recipe->meals()->pluck('recipe_meals.id')->all());
    }

    #[Test]
    public function itAddsAndRemovesFeatures(): void
    {
        $vegan = $this->create(RecipeFeature::class, ['feature' => 'Vegan']);
        $highProtein = $this->create(RecipeFeature::class, ['feature' => 'High Protein']);

        $this->recipe->features()->attach($vegan->id);

        $this->editPage()->fillForm(['features' => [$highProtein->id]])->call('save')->assertHasNoFormErrors();

        $this->assertSame([$highProtein->id], $this->recipe->features()->pluck('recipe_features.id')->all());
    }

    #[Test]
    public function itHidesTheLegacyDairyFreeFieldOnARecipeWithoutOne(): void
    {
        $this->recipe->update(['df_to_not_df' => '']);

        $this->editPage()->assertSchemaComponentHidden('df_to_not_df');
    }

    #[Test]
    public function itShowsTheLegacyDairyFreeFieldOnARecipeThatHasOne(): void
    {
        $this->recipe->update(['df_to_not_df' => 'Swap the butter for a dairy free spread']);

        $this->editPage()
            ->assertSchemaComponentVisible('df_to_not_df')
            ->assertSchemaComponentStateSet('df_to_not_df', 'Swap the butter for a dairy free spread');
    }

    #[Test]
    public function itCanClearTheLegacyDairyFreeField(): void
    {
        $this->recipe->update(['df_to_not_df' => 'Swap the butter for a dairy free spread']);

        $this->editPage()->fillForm(['df_to_not_df' => null])->call('save')->assertHasNoFormErrors();

        $this->assertNull($this->recipe->refresh()->df_to_not_df);
    }

    #[Test]
    public function itFillsTheFaqsFromTheRecipe(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $this->build(Faq::class)->on($this->recipe)->create(['question' => 'Can I freeze it?', 'answer' => 'Yes.']);

        $faqs = $this->editPage()->instance()->form->getRawState()['faqs'];
        $faq = reset($faqs);

        $this->assertCount(1, $faqs);
        $this->assertSame('Can I freeze it?', $faq['question']);
        $this->assertSame('Yes.', $faq['answer']);

        $undoRepeaterFake();
    }

    #[Test]
    public function itAddsAndRemovesFaqs(): void
    {
        $undoRepeaterFake = Repeater::fake();

        $this->build(Faq::class)->on($this->recipe)->create(['question' => 'Can I freeze it?', 'answer' => 'Yes.']);

        $this->editPage()
            ->fillForm(['faqs' => [['question' => 'Can I make it dairy free?', 'answer' => 'Yes.']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount(Faq::class, 1);
        $this->assertSame('Can I make it dairy free?', $this->recipe->faqs()->first()->question);

        $undoRepeaterFake();
    }

    #[Test]
    public function itUnpublishesARecipeMovedBackToDraft(): void
    {
        $this->recipe->update(['publish_at' => Carbon::now()]);

        $this->editPage()->fillForm(['status' => 'draft'])->call('save')->assertHasNoFormErrors();

        $this->recipe->refresh();

        $this->assertFalse($this->recipe->live);
        $this->assertNull($this->recipe->publish_at);
    }

    #[Test]
    public function itSendsTheUserBackToTheRecipeListAfterSaving(): void
    {
        $this->editPage()->call('save')->assertRedirect(RecipeResource::getUrl('index'));
    }

    #[Test]
    public function itCanEditARecipeThatIsNotLive(): void
    {
        $recipe = $this->build(Recipe::class)->notLive()->create();

        Livewire::test(EditRecipe::class, ['record' => $recipe->getRouteKey()])->assertOk();
    }

    #[Test]
    public function itCanOpenARecipeThatHasNoNutritionRow(): void
    {
        $recipe = $this->create(Recipe::class);

        $this->assertNull($recipe->nutrition);

        Livewire::test(EditRecipe::class, ['record' => $recipe->getRouteKey()])->assertOk();
    }

    #[Test]
    public function itCreatesTheNutritionRowForARecipeThatHasNone(): void
    {
        $recipe = $this->create(Recipe::class);

        $this->addImages($recipe);

        Livewire::test(EditRecipe::class, ['record' => $recipe->getRouteKey()])
            ->fillForm(['nutrition' => ['calories' => 200, 'carbs' => 10, 'fat' => 5, 'protein' => 3, 'fibre' => 1, 'sugar' => 8]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(200, $recipe->refresh()->nutrition->calories);
    }

    /** @return array<int, int> */
    protected function tickedAllergens(): array
    {
        $state = $this->editPage()->instance()->form->getRawState()['allergens'];

        return collect($state)->map(fn ($id): int => (int) $id)->sort()->values()->all();
    }

    /** @return array<int, int> */
    protected function freeFromAllergens(): array
    {
        return $this->recipe->allergens()->pluck('recipe_allergens.id')->sort()->values()->all();
    }

    protected function allergens()
    {
        return collect([
            $this->create(RecipeAllergen::class, ['allergen' => 'Dairy']),
            $this->create(RecipeAllergen::class, ['allergen' => 'Egg']),
            $this->create(RecipeAllergen::class, ['allergen' => 'Soya']),
        ]);
    }

    protected function addImages(Recipe $recipe): void
    {
        $recipe->addMedia(UploadedFile::fake()->image('header.jpg'))->toMediaCollection('primary');
        $recipe->addMedia(UploadedFile::fake()->image('square.jpg'))->toMediaCollection('square');
        $recipe->addMedia(UploadedFile::fake()->image('social.jpg'))->toMediaCollection('social');
    }

    protected function editPage(): Testable
    {
        return Livewire::test(EditRecipe::class, ['record' => $this->recipe->getRouteKey()]);
    }
}
