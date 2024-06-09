<?php

declare(strict_types=1);

namespace App\Models\Recipes;

use App\Concerns\CanBePublished;
use App\Concerns\Comments\Commentable;
use App\Concerns\DisplaysDates;
use App\Concerns\DisplaysMedia;
use App\Concerns\LinkableModel;
use App\Contracts\Comments\HasComments;
use App\Contracts\Search\IsSearchable;
use App\Legacy\HasLegacyImage;
use App\Legacy\Imageable;
use App\Scopes\LiveScope;
use App\Support\Collections\CanBeCollected;
use App\Support\Collections\Collectable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\SchemaOrg\Recipe as RecipeSchema;
use Spatie\SchemaOrg\RestrictedDiet;
use Spatie\SchemaOrg\Schema;

/**
 * @property string $servings
 * @property string $portion_size
 */
class Recipe extends Model implements Collectable, HasComments, HasMedia, IsSearchable
{
    use CanBeCollected;
    use CanBePublished;
    use Commentable;
    use DisplaysDates;
    use DisplaysMedia;
    use HasLegacyImage;
    use Imageable;
    use InteractsWithMedia;
    use LinkableModel;
    use Searchable;

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('social')->singleFile();

        $this->addMediaCollection('primary')->singleFile();

        $this->addMediaCollection('square')->singleFile();

        $this->addMediaCollection('body');
    }

    /** @return BelongsToMany<RecipeAllergen> */
    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(
            RecipeAllergen::class,
            'recipe_assigned_allergens',
            'recipe_id',
            'allergen_type_id'
        )->withTimestamps();
    }

    /** @return Collection<int, RecipeAllergen> */
    public function containsAllergens(): Collection
    {
        return RecipeAllergen::query()
            ->get()
            ->reject(fn (RecipeAllergen $allergen) => $this->allergens->where('allergen', $allergen->allergen)->count() > 0);
    }

    /** @return BelongsToMany<RecipeFeature> */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(
            RecipeFeature::class,
            'recipe_assigned_features',
            'recipe_id',
            'feature_type_id'
        )->withTimestamps();
    }

    /** @return BelongsToMany<RecipeMeal> */
    public function meals(): BelongsToMany
    {
        return $this->belongsToMany(
            RecipeMeal::class,
            'recipe_assigned_meals',
            'recipe_id',
            'meal_type_id'
        )->withTimestamps();
    }

    /** @return Attribute<string, never-return> */
    public function servings(): Attribute
    {
        return Attribute::get(fn () => $this->serving_size);
    }

    /** @return Attribute<string, never-return> */
    public function portionSize(): Attribute
    {
        return Attribute::get(fn () => $this->per);
    }

    public function schema(): RecipeSchema
    {
        /** @var string $url */
        $url = config('app.url');

        return Schema::recipe()
            ->name($this->title)
            ->image($this->main_image)
            ->author(Schema::person()->name('Alison Peters'))
            ->dateModified($this->updated_at)
            ->datePublished($this->created_at)
            ->prepTime($this->formatTimeToIso($this->prep_time)) /** @phpstan-ignore-line */
            ->cookTime($this->formatTimeToIso($this->cook_time)) /** @phpstan-ignore-line */
            ->recipeYield($this->servings)
            ->nutrition(
                Schema::nutritionInformation()
                    ->calories("{$this->nutrition->calories} calories") /** @phpstan-ignore-line */
                    ->carbohydrateContent("{$this->nutrition->carbs} grams") /** @phpstan-ignore-line */
                    ->fatContent("{$this->nutrition->fat} grams") /** @phpstan-ignore-line */
                    ->proteinContent("{$this->nutrition->protein} grams") /** @phpstan-ignore-line */
                    ->sugarContent("{$this->nutrition->sugar} grams") /** @phpstan-ignore-line */
                    ->fiberContent("{$this->nutrition->fibre} grams") /** @phpstan-ignore-line */
                    ->servingSize($this->portion_size)
            )
            ->recipeIngredient(explode("\n", $this->ingredients))
            ->recipeInstructions(explode("\n\n", $this->method))
            ->suitableForDiet($this->richTextSuitableFor())
            ->keywords($this->meta_tags)
            ->recipeCategory('Gluten Free')
            ->recipeCuisine('')
            ->mainEntityOfPage(Schema::webPage()->identifier($url))
            ->publisher(
                Schema::organization()
                    ->name('Coeliac Sanctuary')
                    ->logo(Schema::imageObject()->url($url . '/images/logo.svg'))
            );
    }

    /**
     * @return HasOne<RecipeNutrition>
     */
    public function nutrition(): HasOne
    {
        return $this->hasOne(RecipeNutrition::class)->latest();
    }

    protected function formatTimeToIso(string $time): string
    {
        $time = str_ireplace([' and', ' a', ' half'], '', $time);
        $bits = explode(' ', $time);

        if (count($bits) === 4) {
            return "PT{$bits[0]}H{$bits[2]}M";
        }

        if (count($bits) === 2) {
            $unit = 'M';

            if (in_array($bits[1], ['Hour', 'Hours'])) {
                $unit = 'H';
            }

            return "PT{$bits[0]}{$unit}";
        }

        return 'PT';
    }

    protected function richTextSuitableFor(): array
    {
        $suitableFor[] = Schema::restrictedDiet()->identifier(RestrictedDiet::GlutenFreeDiet);

        if ( ! $this->allergens->contains('Dairy')) {
            $suitableFor[] = Schema::restrictedDiet()->identifier(RestrictedDiet::LowLactoseDiet);
        }

        if ($this->nutrition?->calories < 400) {
            $suitableFor[] = Schema::restrictedDiet()->identifier(RestrictedDiet::LowCalorieDiet);
        }

        if ($this->features->contains('Vegan')) {
            $suitableFor[] = Schema::restrictedDiet()->identifier(RestrictedDiet::VeganDiet);
        }

        if ($this->features->contains('Vegetarian')) {
            $suitableFor[] = Schema::restrictedDiet()->identifier(RestrictedDiet::VegetarianDiet);
        }

        return $suitableFor;
    }

    /**
     * @param  Builder<Recipe>  $builder
     * @return Builder<Recipe>
     */
    public function scopeHasFeatures(Builder $builder, array $features): Builder
    {
        return $builder->where(function (Builder $builder) use ($features) {
            foreach ($features as $feature) {
                $builder->whereHas('features', fn (Builder $builder) => $builder->where('slug', $feature));
            }

            return $builder;
        });
    }

    /**
     * @param  Builder<Recipe>  $builder
     * @return Builder<Recipe>
     */
    public function scopeHasMeals(Builder $builder, array $meals): Builder
    {
        return $builder->where(function (Builder $builder) use ($meals) {
            foreach ($meals as $meal) {
                $builder->whereHas('meals', fn (Builder $builder) => $builder->where('slug', $meal));
            }

            return $builder;
        });
    }

    /**
     * @param  Builder<Recipe>  $builder
     * @return Builder<Recipe>
     */
    public function scopeHasFreeFrom(Builder $builder, array $freeFrom): Builder
    {
        return $builder->where(function (Builder $builder) use ($freeFrom) {
            foreach ($freeFrom as $allergen) {
                $builder->whereHas('allergens', fn (Builder $builder) => $builder->where('slug', $allergen));
            }

            return $builder;
        });
    }

    public function toSearchableArray(): array
    {
        return $this->transform([
            'title' => $this->title,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'metaTags' => $this->meta_tags,
            'updated_at' => $this->updated_at,
            'freefrom' => $this->allergens()->get()->transform(fn ($allergen) => $allergen->allergen)->join(', '),
            'features' => $this->features()->get()->transform(fn ($feature) => $feature->feature)->join(', '),
        ]);
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->live;
    }

    public function getScoutKey(): mixed
    {
        return $this->id;
    }
}
