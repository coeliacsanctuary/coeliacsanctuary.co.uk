<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes\Schemas;

use App\Filament\Resources\MainSite\Recipes\Pages\CreateRecipe;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        Storage::fake('media');

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itAcceptsACompleteRecipe(): void
    {
        $this->fillCreateForm()->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    #[DataProvider('invalidRecipes')]
    public function itValidatesTheRecipe(array $overrides, array $errors): void
    {
        $this->fillCreateForm($overrides)
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    }

    public static function invalidRecipes(): array
    {
        return [
            '`title` is required' => [['title' => null], ['title' => 'required']],
            '`title` is max 200 characters' => [['title' => Str::random(201)], ['title' => 'max']],
            '`slug` is required' => [['slug' => null], ['slug' => 'required']],
            '`slug` is max 200 characters' => [['slug' => Str::repeat('a', 201)], ['slug' => 'max']],
            '`slug` rejects capitals' => [['slug' => 'Victoria-Sponge'], ['slug' => 'regex']],
            '`slug` rejects spaces' => [['slug' => 'victoria sponge'], ['slug' => 'regex']],
            '`slug` rejects underscores' => [['slug' => 'victoria_sponge'], ['slug' => 'regex']],
            '`slug` rejects full stops' => [['slug' => 'victoria.sponge'], ['slug' => 'regex']],
            '`short_title` is max 100 characters' => [['short_title' => Str::random(101)], ['short_title' => 'max']],
            '`description` is required' => [['description' => null], ['description' => 'required']],
            '`search_tags` is required' => [['search_tags' => null], ['search_tags' => 'required']],
            '`author` is required' => [['author' => null], ['author' => 'required']],
            '`author` is max 255 characters' => [['author' => Str::random(256)], ['author' => 'max']],
            '`meta_tags` is required' => [['meta_tags' => null], ['meta_tags' => 'required']],
            '`meta_description` is required' => [['meta_description' => null], ['meta_description' => 'required']],
            '`ingredients` is required' => [['ingredients' => null], ['ingredients' => 'required']],
            '`method` is required' => [['method' => null], ['method' => 'required']],
            '`prep_time` is required' => [['prep_time' => null], ['prep_time' => 'required']],
            '`prep_time` is max 50 characters' => [['prep_time' => Str::random(51)], ['prep_time' => 'max']],
            '`cook_time` is required' => [['cook_time' => null], ['cook_time' => 'required']],
            '`cook_time` is max 50 characters' => [['cook_time' => Str::random(51)], ['cook_time' => 'max']],
            '`serving_size` is required' => [['serving_size' => null], ['serving_size' => 'required']],
            '`serving_size` is max 50 characters' => [['serving_size' => Str::random(51)], ['serving_size' => 'max']],
            '`per` is required' => [['per' => null], ['per' => 'required']],
            '`per` is max 50 characters' => [['per' => Str::random(51)], ['per' => 'max']],
            '`header` is required' => [['header' => []], ['header' => 'required']],
            '`square` is required' => [['square' => []], ['square' => 'required']],
            '`social` is required' => [['social' => []], ['social' => 'required']],
        ];
    }

    #[Test]
    #[DataProvider('nutritionFields')]
    public function itRequiresEveryNutritionalValue(string $field): void
    {
        $this->fillCreateForm(['nutrition' => [...static::nutrition(), $field => null]])
            ->call('create')
            ->assertHasFormErrors(["nutrition.{$field}" => 'required']);
    }

    #[Test]
    #[DataProvider('nutritionFields')]
    public function itRequiresEveryNutritionalValueToBeANumber(string $field): void
    {
        $this->fillCreateForm(['nutrition' => [...static::nutrition(), $field => 'lots']])
            ->call('create')
            ->assertHasFormErrors(["nutrition.{$field}" => 'numeric']);
    }

    public static function nutritionFields(): array
    {
        return [
            'calories' => ['calories'],
            'carbs' => ['carbs'],
            'fat' => ['fat'],
            'protein' => ['protein'],
            'fibre' => ['fibre'],
            'sugar' => ['sugar'],
        ];
    }

    #[Test]
    public function itRejectsASlugThatIsAlreadyTaken(): void
    {
        $this->create(Recipe::class, ['slug' => 'gluten-free-victoria-sponge']);

        $this->fillCreateForm(['slug' => 'gluten-free-victoria-sponge'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    #[Test]
    public function itAcceptsALowercaseHyphenatedSlug(): void
    {
        $this->fillCreateForm(['slug' => 'gluten-free-sponge-2026'])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    public function itAcceptsARecipeWithNoBody(): void
    {
        $this->fillCreateForm(['body' => null])->call('create')->assertHasNoFormErrors();
    }

    #[Test]
    public function itRejectsARawIframeInTheBody(): void
    {
        $this->fillCreateForm(['body' => '<p>Watch this</p><iframe src="https://example.com"></iframe>'])
            ->call('create')
            ->assertHasFormErrors(['body']);
    }

    #[Test]
    public function itRejectsMismatchedTagCasingInTheBody(): void
    {
        $this->fillCreateForm(['body' => '<p>Some text</P>'])
            ->call('create')
            ->assertHasFormErrors(['body']);
    }

    #[Test]
    public function itRequiresAPublishDateWhenScheduling(): void
    {
        $this->fillCreateForm(['status' => 'scheduled', 'publish_at' => null])
            ->call('create')
            ->assertHasFormErrors(['publish_at' => 'required']);
    }

    #[Test]
    public function itDoesNotRequireAPublishDateOtherwise(): void
    {
        $this->fillCreateForm(['status' => 'draft', 'publish_at' => null])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    public function itSlugsTheTitleWhenTheSlugIsEmpty(): void
    {
        Livewire::test(CreateRecipe::class)
            ->fillForm(['title' => 'Gluten Free Victoria Sponge'])
            ->assertSchemaStateSet(['slug' => 'gluten-free-victoria-sponge']);
    }

    #[Test]
    public function itFollowsTheTitleWhileTheSlugStillMatchesIt(): void
    {
        Livewire::test(CreateRecipe::class)
            ->fillForm(['title' => 'Gluten Free Victoria Sponge'])
            ->fillForm(['title' => 'Gluten Free Chocolate Cake'])
            ->assertSchemaStateSet(['slug' => 'gluten-free-chocolate-cake']);
    }

    #[Test]
    public function itLeavesASlugThatHasBeenEditedByHand(): void
    {
        Livewire::test(CreateRecipe::class)
            ->fillForm(['title' => 'Gluten Free Victoria Sponge'])
            ->fillForm(['slug' => 'my-own-slug'])
            ->fillForm(['title' => 'Gluten Free Chocolate Cake'])
            ->assertSchemaStateSet(['slug' => 'my-own-slug']);
    }

    #[Test]
    public function theSlugCanBeSetWhenCreating(): void
    {
        Livewire::test(CreateRecipe::class)
            ->assertSchemaComponentExists('slug', checkComponentUsing: fn (TextInput $f): bool => ! $f->isDisabled());
    }

    #[Test]
    public function itExplainsWhatTheShortTitleIsFor(): void
    {
        Livewire::test(CreateRecipe::class)->assertSchemaComponentExists(
            'short_title',
            checkComponentUsing: fn (Field $f): bool => $this->helperText($f) === 'Optional, used with FAQs',
        );
    }

    #[Test]
    public function itHidesTheLegacyDairyFreeFieldWhenCreating(): void
    {
        Livewire::test(CreateRecipe::class)->assertSchemaComponentHidden('df_to_not_df');
    }

    #[Test]
    public function itExplainsWhichWayRoundTheAllergensAreTicked(): void
    {
        $section = collect(Livewire::test(CreateRecipe::class)->instance()->form->getFlatComponents(withHidden: true))
            ->first(fn ($component): bool => $component instanceof Section && $component->getHeading() === 'Allergens');

        $this->assertSame('Tick the allergens that apply to this recipe.', $section->getDescription());
    }

    #[Test]
    public function itStartsWithNoFaqRows(): void
    {
        Livewire::test(CreateRecipe::class)->assertSchemaStateSet(['faqs' => []]);
    }

    protected function helperText(Field $field): string
    {
        $components = $field->getChildSchema(Field::BELOW_CONTENT_SCHEMA_KEY)?->getComponents() ?? [];

        return $components === [] ? '' : (string) $components[0]->getContent();
    }

    protected function fillCreateForm(array $overrides = []): Testable
    {
        return Livewire::test(CreateRecipe::class)->fillForm($this->validFormData($overrides));
    }

    /** @return array<string, int> */
    protected static function nutrition(): array
    {
        return [
            'calories' => 350,
            'carbs' => 40,
            'fat' => 18,
            'protein' => 5,
            'fibre' => 2,
            'sugar' => 25,
        ];
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
            'nutrition' => static::nutrition(),
            'header' => [UploadedFile::fake()->image('header.jpg')],
            'square' => [UploadedFile::fake()->image('square.jpg')],
            'social' => [UploadedFile::fake()->image('social.jpg')],
            ...$overrides,
        ];
    }
}
