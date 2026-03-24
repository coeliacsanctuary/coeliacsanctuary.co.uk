<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut;

use App\Models\EatingOut\EateryCollection as EateryCollectionModel;
use App\Nova\Resource;
use App\Nova\Support\Panels\VisibilityPanel;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Jpeters8889\EateryCollectionsQueryBuilder\EateryCollectionsQueryBuilder;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * @codeCoverageIgnore
 */
class EateryCollections extends Resource
{
    /** @var class-string<EateryCollectionModel> */
    public static string $model = EateryCollectionModel::class;

    public static $title = 'title';

    public static $search = ['id', 'title'];

    public static $with = ['media'];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id'),

            new Panel('Introduction', [
                Text::make('Title')->fullWidth()->rules(['required', 'max:200'])->showWhenPeeking(),

                Slug::make('Slug')->from('Title')
                    ->hideFromIndex()
                    ->hideWhenUpdating()
                    ->showOnCreating()
                    ->fullWidth()
                    ->rules(['required', 'max:200', 'unique:wheretoeat_collections,slug']),

                Textarea::make('Description')->onlyOnForms()->fullWidth()->rules(['required'])->showWhenPeeking(),
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

                Images::make('Social Image', 'social')
                    ->onlyOnForms()
                    ->addButtonLabel('Select Social Image')
                    ->rules(['required']),
            ]),

            new Panel('Content', [
                Textarea::make('Body')
                    ->rows(20)
                    ->fullWidth()
                    ->rules(['required']),
            ]),

            new Panel('Configuration', [
                EateryCollectionsQueryBuilder::make('Configuration')
                    ->fullWidth()
                    ->onlyOnForms(),
            ]),

            DateTime::make('Created At')->sortable()->exceptOnForms(),

            DateTime::make('Updated At')->sortable()->exceptOnForms(),

            URL::make('View', fn ($blog) => $blog->live ? $blog->link : null)
                ->exceptOnForms(),
        ];
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $eateryCollection = $fillFields[0];

        if ($eateryCollection->_status === 'draft') {
            $eateryCollection->publish_at = null;
        }

        $eateryCollection->live = $eateryCollection->_status === 'live';

        unset($eateryCollection->_status);

        return $fillFields;
    }

    public static function indexQuery(NovaRequest $request, Builder $query)
    {
        return $query->withoutGlobalScopes();
    }
}
