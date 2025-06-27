<?php

declare(strict_types=1);

use App\Models\Comments\CommentReply;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        CommentReply::query()
            ->whereLike('comment_reply', '%http%')
            ->whereNotLike('comment_reply', '%<a href=%')
            ->get()
            ->each(fn (CommentReply $comment) => $comment->update([
                'comment_reply' => preg_replace(
                    '/(?<!href=")(https?:\/\/[^\s<]+)(?![^<]*<\/a>)/i',
                    '<a href="$1" target="_blank" rel="noopener">$1</a>',
                    $comment->comment_reply
                ),
            ]));
    }
};
