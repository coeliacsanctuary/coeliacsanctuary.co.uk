<?php

declare(strict_types=1);

namespace App\Models\Comments;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Concerns\DisplaysDates;
use App\Contracts\Comments\HasComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @param  HasComments  $commentable
 */
#[Appends(['what'])]
class Comment extends Model
{
    use DisplaysDates;

    protected $attributes = [
        'approved' => false,
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

    /** @return Attribute<string, never> */
    protected function what(): Attribute
    {
        return Attribute::make(get: fn (): string => class_basename($this->commentable_type));
    }
    protected function casts(): array
    {
        return [
            'approved' => 'bool',
        ];
    }
}
