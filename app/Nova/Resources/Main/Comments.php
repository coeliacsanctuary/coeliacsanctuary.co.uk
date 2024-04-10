<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main;

use App\Models\Comments\Comment;
use App\Models\Recipes\RecipeAllergen;
use App\Nova\Actions\EatingOut\ApproveComment;
use App\Nova\Actions\EatingOut\ReplyToCommentComment;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<RecipeAllergen> */
/**
 * @codeCoverageIgnore
 */
class Comments extends Resource
{
    public static string $model = Comment::class;

    public static $clickAction = 'view';

    public function authorizedToView(Request $request)
    {
        return true;
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->hidden(),

            MorphTo::make('Resource', 'commentable')
                ->types([
                    Blog::class,
                    Recipe::class,
                ])
                ->viewable()
                ->filterable()
                ->peekable(),

            Text::make('Name'),

            Textarea::make('Comment')->alwaysShow(),

            Boolean::make('Has Reply', fn () => $this->resource->reply?->exists)->exceptOnForms(),

            Boolean::make('Approved'),

            DateTime::make('Added', 'created_at'),

            HasOne::make('Reply', resource: CommentReply::class),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            ApproveComment::make()->showInline()->canRun(fn ($request, Comment $review) => $review->approved === false),
            ReplyToCommentComment::make()->sole()->showInline()->canRun(fn ($request, Comment $review) => $review->approved === false),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->withoutGlobalScopes()->with('reply');
    }
}
