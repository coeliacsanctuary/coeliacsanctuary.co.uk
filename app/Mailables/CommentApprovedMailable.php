<?php

declare(strict_types=1);

namespace App\Mailables;

use App\Infrastructure\MjmlMessage;
use App\Models\Comments\Comment;

class CommentApprovedMailable extends BaseMailable
{
    public function __construct(protected Comment $comment, ?string $emailKey = null)
    {
        parent::__construct($emailKey);
    }

    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject("Your comment on {$this->comment->commentable->title} on Coeliac Sanctuary has been approved!") /** @phpstan-ignore-line */
            ->mjml('mailables.mjml.comment-approved', $this->baseData([
                'comment' => $this->comment,
                'reason' => 'to let you know your comment on Coeliac Sanctuary has been approved.',
                'email' => $this->comment->email,
            ]));
    }
}
