<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Blogs\Blog as BlogModel;
use App\Nova\Resource;
use App\Nova\Support\Panels\VisibilityPanel;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Jpeters8889\Body\Body;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/** @extends resource<BlogModel> */
/**
 * @codeCoverageIgnore
 */
class Blog extends Resource
{
    /** @var class-string<BlogModel> */
    public static string $model = BlogModel::class;

    public static $title = 'title';

    public static $search = ['id', 'title'];

    public static $with = ['tags', 'media'];

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
                    ->rules(['required', 'max:200', 'unique:blogs,slug']),

                Tag::make('Tags', 'tags', BlogTag::class) /** @phpstan-ignore-line */
                    ->showCreateRelationButton()
                    ->fullWidth(),

                Select::make('Primary Tag', 'primary_tag_id')
                    ->resolveUsing(function ($value) {
                        if ( ! $value) {
                            return;
                        }

                        $tag = \App\Models\Blogs\BlogTag::query()->withCount('blogs')->find($value);

                        if ($tag) {
                            return "{$tag->tag} - ({$tag->blogs_count} blogs)";
                        }
                    })
                    ->dependsOn(['tags'], function (Select $field, NovaRequest $request, FormData $data): void {
                        $tags = collect(json_decode($data->get('tags')))->map(fn ($tag) => $tag->display);

                        if ($tags->isEmpty()) {
                            $field->readonly()->help('Please add tags before selecting a primary tag.');

                            return;
                        }

                        $field->readonly(false)
                            ->options($tags->mapWithKeys(fn ($tag) => [$tag => $tag])->toArray())
                            ->displayUsingLabels()
                            ->help('If set, the primary tag will be used for related blogs in the sidebar');
                    })
                    ->nullable(),

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

                Images::make('Body Images', 'body')
                    ->onlyOnForms()
                    ->insertable()
                    ->fullSize(),
            ]),

            new Panel('Content', [
                Body::make('Body')
                    ->canHaveImages()
                    ->mustBeValidHtml()
                    ->fullWidth()
                    ->rules(['required']),

                Boolean::make('Show Author')
                    ->default(true)
                    ->onlyOnForms()
                    ->fullWidth()
                    ->help('If checked, the about Alison block will be shown at the bottom of the blog.'),
            ]),

            DateTime::make('Created At')->sortable()->exceptOnForms(),

            DateTime::make('Updated At')->sortable()->exceptOnForms(),

            URL::make('View', fn ($blog) => $blog->live ? $blog->link : null)
                ->exceptOnForms(),

            MorphMany::make('comments', resource: Comments::class),
        ];
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        $fillFields = parent::fillFields($request, $model, $fields);
        $blog = $fillFields[0];

        if ($blog->_status === 'draft') {
            $blog->publish_at = null;
        }

        $blog->live = $blog->_status === 'live';

        unset($blog->_status);

        if ($blog->primary_tag_id && is_string($blog->primary_tag_id)) {
            $tag = Str::beforeLast($blog->primary_tag_id, ' - (');

            $tagModel = \App\Models\Blogs\BlogTag::query()->where('tag', $tag)->first();

            $blog->primary_tag_id = $tagModel?->id;
        }

        return $fillFields;
    }

    public static function indexQuery(NovaRequest $request, Builder $query)
    {
        return $query->withoutGlobalScopes();
    }
}
