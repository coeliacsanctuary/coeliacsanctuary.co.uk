<?php

declare(strict_types=1);

namespace App\Concerns\Comments;

use App\Models\Comments\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @template T of Model
 * @mixin Model
 * @property string $title
 */
trait Commentable
{
    /** @return MorphMany<Comment, T> */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->where('approved', true)->orderByDesc('created_at');
    }

    /** @return MorphMany<Comment, T> */
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
