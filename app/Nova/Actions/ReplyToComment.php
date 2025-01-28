<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Comments\Comment;
use App\Notifications\CommentRepliedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class ReplyToComment extends Action
{
    public $name = 'Reply & Approve';

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (Comment $comment) use ($fields): void {
            if ($comment->approved) {
                return;
            }

            $reply = $comment->reply()->create([
                'comment_reply' => $fields->reply,
            ]);

            $comment->update(['approved' => true]);

            (new AnonymousNotifiable())
                ->route('mail', $comment->email)
                ->notify(new CommentRepliedNotification($reply));
        });
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Textarea::make('Reply'),
        ];
    }
}
