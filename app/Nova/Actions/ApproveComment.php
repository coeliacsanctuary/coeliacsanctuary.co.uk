<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Comments\Comment;
use App\Notifications\CommentApprovedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class ApproveComment extends Action
{
    public $name = 'Approve';

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        $models->each(function (Comment $comment): void {
            if ($comment->approved) {
                return;
            }

            $comment->update(['approved' => true]);

            new AnonymousNotifiable()
                ->route('mail', $comment->email)
                ->notify(new CommentApprovedNotification($comment));
        });
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
