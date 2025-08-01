<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Blogs\Blog as BlogModel;
use App\Nova\Resource;
use App\Nova\Support\Panels\VisibilityPanel;
use Jpeters8889\AdvancedNovaMediaLibrary\Fields\Images;
use Jpeters8889\Body\Body;
use Jpeters8889\NovaAi\FieldAi;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
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

                Textarea::make('Description')->onlyOnForms()->fullWidth()->rules(['required'])->showWhenPeeking(),
            ]),

            VisibilityPanel::make(),

            new Panel('Metas', [
                Text::make('Meta Tags')->onlyOnForms()->fullWidth()->rules(['required']),

                Textarea::make('Meta Description')
                    ->rows(2)
                    ->fullWidth()
                    ->alwaysShow()
                    ->usingAi(fn (FieldAi $fieldAi) => $fieldAi
                        ->relyOn(['Title', 'Body'])
                        ->setScenario('You are an AI helper for writing blogs on a website called Coeliac Sanctuary, the website serves the Coeliac/gluten free community.')
                        ->prompt(fn (Textarea $field, FormData $data) => <<<Prompt
                            Using the below blog title and blog body, please generate a meta description. Please stick to common conventions for meta descriptions, keeping SEO in mind.

                            Title:
                            {$data->get('Title')}

                            Body:
                            {$data->get('Body')}
                            Prompt)
                    )
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
}
