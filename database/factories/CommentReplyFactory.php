<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Contracts\Comments\HasComments;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReply;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comments\CommentReply>
 */
class CommentReplyFactory extends Factory
{
    protected $model = CommentReply::class;

    public function definition()
    {
        return [
            'comment_reply' => $this->faker->paragraph(),
            'comment_id' => static::factoryForModel(Comment::class),
        ];
    }

    public function on(HasComments $commentable)
    {
        return $this->state([
            'comment_id' => static::factoryForModel(Comment::class)->on($commentable)
        ]);
    }
}
