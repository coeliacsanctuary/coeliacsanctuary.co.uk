<?php

declare(strict_types=1);

namespace App\Models\Comments;

use App\Concerns\DisplaysDates;
use App\Contracts\Comments\HasComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @param  HasComments  $commentable
 */
class Comment extends Model
{
    use DisplaysDates;

    protected $appends = ['what'];

    protected $attributes = [
        'approved' => false,
    ];

    protected $casts = [
        'approved' => 'bool',
    ];

    /** @return HasOne<CommentReply, $this> */
    public function reply(): HasOne
    {
        return $this->hasOne(CommentReply::class);
    }

    /** @return MorphTo<Model, $this> */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getWhatAttribute(): string
    {
        return class_basename($this->commentable_type);
    }
}
