<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Recipes\Recipe as RecipeModel;
use App\Nova\Chartables\Metrics\Recipes\CollectionCardViews;
use App\Nova\Chartables\Metrics\Recipes\CommentViews;
use App\Nova\Chartables\Metrics\Recipes\DetailCardViews;
use App\Nova\Chartables\Metrics\Recipes\Views;
use App\Nova\Repeaters\Faq as FaqRepeatable;
use App\Nova\Resource;
use App\Nova\Resources\Main\PolymorphicPanels\RecipeAllergens as RecipeAllergenPanel;
use App\Nova\Resources\Main\PolymorphicPanels\RecipeFeatures as RecipeFeaturePanel;
use App\Nova\Resources\Main\PolymorphicPanels\RecipeMeals as RecipeMealPanel;
use App\Nova\Support\Panels\VisibilityPanel;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Jpeters8889\ApexCharts\ApexChart;
use Jpeters8889\Body\Body;
use Jpeters8889\PolymorphicPanel\PolymorphicPanel;
use Jpeters8889\RelatedRecipesSearch\RelatedRecipesSearch;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use App\Nova\FieldOverrides\Repeater;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Throwable;

/** @extends resource<RecipeModel> */
/**
 * @codeCoverageIgnore
 */
class Recipe extends Resource
{
    public static string $model = RecipeModel::class;

    public static $title = 'title';

    public static $search = ['id', 'title'];

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id'),

            new Panel('Introduction', [
                Text::make('Title')->fullWidth()->rules(['required', 'max:200']),

                Slug::make('Slug')->from('Title')
                    ->hideFromIndex()
                    ->hideWhenUpdating()
                    ->showOnCreating()
                    ->fullWidth()
                    ->rules(['sometimes', 'required', 'max:200', 'unique:recipes,slug']),

                Text::make('Short Title')
                    ->fullWidth()
                    ->maxlength(100)
                    ->onlyOnForms()
                    ->help('Optional, used with FAQs')
                    ->nullable(),

                Text::make('Search Tags')->onlyOnForms()->fullWidth()->rules(['required']),

                Textarea::make('Description')->onlyOnForms()->fullWidth()->rules(['required']),

                Text::make('Author')->onlyOnForms()->fullWidth()->rules(['required', 'max:255']),
            ]),

            VisibilityPanel::make(),

            new Panel('Metas', [
                Text::make('Meta Tags')->onlyOnForms()->fullWidth()->rules(['required']),

                Textarea::make('Meta Description')
                    ->rows(2)
                    ->fullWidth()
                    ->alwaysShow()
                    ->rules(['required']),
            ]),

            new Panel('Images', [
                Images::make('Header Image', 'primary')
                    ->onlyOnForms()
                    ->addButtonLabel('Select Header Image')
                    ->rules(['required']),

                Text::make('Header Image Alt Text', 'header_image_alt_text')
                    ->nullable()
                    ->onlyOnForms()
                    ->fullWidth()
                    ->help('Descriptive alt text for the header image. Defaults to the recipe title if left blank.'),

                Images::make('Square Image', 'square')
                    ->onlyOnForms()
                    ->addButtonLabel('Select Square Image')
                    ->rules(['required']),

                Images::make('Social Image', 'social')
                    ->onlyOnForms()
                    ->addButtonLabel('Select Social Image')
                    ->rules(['required']),

                Images::make('Body Images', 'body')
                    ->onlyOnForms()
                    ->insertable()
                    ->fullSize(),
            ]),

            new Panel('Body', [
                Body::make('Body')
                    ->fullWidth()
                    ->canHaveImages()
                    ->mustBeValidHtml()
                    ->nullable(),
            ]),

            new Panel('Recipe', [
                Text::make('Prep Time')->onlyOnForms()->fullWidth()->rules(['required', 'max:50']),

                Text::make('Cook Time')->onlyOnForms()->fullWidth()->rules(['required', 'max:50']),

                Body::make('Ingredients')
                    ->fullWidth()
                    ->noToolbar()
                    ->rows(15)
                    ->rules(['required']),

                Body::make('Method')
                    ->fullWidth()
                    ->noToolbar()
                    ->rules(['required']),

                Text::make('Serving Size')->onlyOnForms()->fullWidth()->rules(['required', 'max:50']),

                Text::make('Nutrition per...', 'per')->onlyOnForms()->fullWidth()->rules(['required', 'max:50']),

                Text::make('DF to not DF', 'df_to_not_df')
                    ->onlyOnForms()
                    ->nullable()
                    ->fullWidth()
                    ->rules(['max:255']),
            ]),

            HasOne::make('Nutritional Information', 'nutrition', RecipeNutritionalInformation::class)->onlyOnForms()->fullWidth(),

            (new Panel('Allergens', [
                PolymorphicPanel::make('Allergens', new RecipeAllergenPanel())->display('row'),
            ]))->help('Tick the allergens that apply to this recipe.'),

            new Panel('Meals', [
                PolymorphicPanel::make('Meals', new RecipeMealPanel())->display('row'),
            ]),

            new Panel('Features', [
                PolymorphicPanel::make('Features', new RecipeFeaturePanel())->display('row'),
            ]),

            Repeater::make('FAQs', 'faqs')
                ->asMorphMany()
                ->repeatables([FaqRepeatable::make()]),

            new Panel('Related Recipes', [
                RelatedRecipesSearch::make('Related Recipes', 'relatedRecipes')
                    ->deferrable()
                    ->fullWidth()
                    ->hideFromIndex()
                    ->hideFromDetail(),
            ]),

            DateTime::make('Created At')->sortable()->exceptOnForms(),

            DateTime::make('Updated At')->sortable()->exceptOnForms(),

            URL::make('View', fn ($recipe) => $recipe->live ? $recipe->link : null)
                ->exceptOnForms(),
        ];
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $recipe = $fillFields[0];

        if ($recipe->_status === 'draft') {
            $recipe->publish_at = null;
        }

        $recipe->live = $recipe->_status === 'live';

        unset($recipe->_status);

        return $fillFields;
    }

    public function cards(NovaRequest $request)
    {
        try {
            $recipeId = $request->findResourceOrFail()->resource->id;

            $metrics = [Views::class, CommentViews::class, DetailCardViews::class, CollectionCardViews::class];

            return array_map(
                fn ($metric) => ApexChart::make($metric)
                    ->withParams(['recipeId' => $recipeId])
                    ->onlyOnDetail()
                    ->fixedHeight()
                    ->fullWidth(),
                $metrics
            );
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function indexQuery(NovaRequest $request, Builder $query)
    {
        return $query->withoutGlobalScopes();
    }
}
