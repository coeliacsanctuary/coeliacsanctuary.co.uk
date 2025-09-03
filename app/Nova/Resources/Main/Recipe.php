<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Recipes\Recipe as RecipeModel;
use App\Nova\Resource;
use App\Nova\Resources\Main\PolymorphicPanels\RecipeAllergens as RecipeAllergenPanel;
use App\Nova\Resources\Main\PolymorphicPanels\RecipeFeatures as RecipeFeaturePanel;
use App\Nova\Resources\Main\PolymorphicPanels\RecipeMeals as RecipeMealPanel;
use App\Nova\Support\Panels\VisibilityPanel;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Jpeters8889\Body\Body;
use Jpeters8889\PolymorphicPanel\PolymorphicPanel;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/** @extends resource<RecipeModel> */
/**
 * @codeCoverageIgnore
 */
class Recipe extends Resource
{
    public static string $model = RecipeModel::class;

    public static $title = 'title';

    public static $search = ['id', 'title'];

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

                Images::make('Square Image', 'square')
                    ->onlyOnForms()
                    ->addButtonLabel('Select Square Image')
                    ->rules(['required']),

                Images::make('Social Image', 'social')
                    ->onlyOnForms()
                    ->addButtonLabel('Select Social Image')
                    ->rules(['required']),
            ]),

            new Panel('Recipe', [
                Text::make('Prep Time')->onlyOnForms()->fullWidth()->rules(['required', 'max:50']),

                Text::make('Cook Time')->onlyOnForms()->fullWidth()->rules(['required', 'max:50']),

                Body::make('Ingredients')
                    ->fullWidth()
                    ->rows(15)
                    ->rules(['required']),

                Body::make('Method')
                    ->fullWidth()
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

            new Panel('Allergens', [
                PolymorphicPanel::make('Allergens', new RecipeAllergenPanel())->display('row'),
            ])->help('Tick the allergens that apply to this recipe.'),

            new Panel('Meals', [
                PolymorphicPanel::make('Meals', new RecipeMealPanel())->display('row'),
            ]),

            new Panel('Features', [
                PolymorphicPanel::make('Features', new RecipeFeaturePanel())->display('row'),
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

        $recipe->live = $recipe->_status === 'live';

        unset($recipe->_status);

        return $fillFields;
    }
}
