<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Collections\Collection as CollectionModel;
use App\Nova\Resource;
use App\Nova\Support\Panels\VisibilityPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Jpeters8889\Body\Body;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/** @extends resource<CollectionModel> */
/**
 * @codeCoverageIgnore
 */
class Collection extends Resource
{
    public static $clickAction = 'view';

    public static string $model = CollectionModel::class;

    public static $title = 'title';

    public static $search = ['id', 'title'];

    public static $with = ['items', 'media'];

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
                    ->rules(['required', 'max:200', 'unique:collections,slug']),

                Textarea::make('Description', 'long_description')->onlyOnForms()->fullWidth()->rules(['required']),
            ]),

            VisibilityPanel::make(),

            new Panel('Display on Homepage', [
                Boolean::make('Display on Homepage')
                    ->dependsOn('live', function (Boolean $field, NovaRequest $request, FormData $formData): void {
                        /** @phpstan-ignore-next-line */
                        if ($formData->live === false) {
                            $field->hide();
                        }
                    })
                    ->filterable(),

                DateTime::make('Hide from homepage on', 'remove_from_homepage')
                    ->dependsOn('display_on_homepage', function (DateTime $field, NovaRequest $request, FormData $formData): void {
                        /** @phpstan-ignore-next-line */
                        if ($formData->display_on_homepage === false) {
                            $field->hide();
                        }
                    })
                    ->nullable(),

                Select::make('Number of items to show on homepage', 'items_to_display')
                    ->dependsOn('display_on_homepage', function (Select $field, NovaRequest $request, FormData $formData): void {
                        /** @phpstan-ignore-next-line */
                        if ($formData->display_on_homepage === false) {
                            $field->hide();
                        }
                    })
                    ->default(3)
                    ->options([
                        '1' => '1 item',
                        '2' => '2 items',
                        '3' => '3 items',
                        '4' => '4 items',
                        '6' => '6 items',
                        '8' => '8 items',
                    ])
                    ->displayUsingLabels()
            ]),

            new Panel('Metas', [
                Text::make('Meta Tags', 'meta_keywords')->onlyOnForms()->fullWidth()->rules(['required']),

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
                Body::make('Body')
                    ->canHaveImages()
                    ->fullWidth()
                    ->nullable(),
            ]),

            HasMany::make('Items', 'items', CollectionItem::class),

            Text::make('Items', fn ($model) => $model->items->count())
                ->exceptOnForms(),

            DateTime::make('Created At')->exceptOnForms(),

            DateTime::make('Updated At')->exceptOnForms(),

            URL::make('View', 'link')->exceptOnForms(),
        ];
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey() . '/' . $resource->getKey();
    }

    /**
     * @param  Builder<CollectionModel>  $query
     * @return Builder<CollectionModel>
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->withoutGlobalScopes()->reorder('updated_at', 'desc');
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $collection = $fillFields[0];

        $collection->live = $collection->_status === 'live';

        unset($collection->_status);

        return $fillFields;
    }
}
