<?php

declare(strict_types=1);

namespace App\Resources\Comments;

use App\Models\Comments\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    /** @return array{name: string, comment: string, published: string, reply: CommentReplyResource | null} */
    public function toArray(Request $request)
    {
        return [
            'name' => $this->name,
            'comment' => $this->comment,
            'published' => $this->published,
            'reply' => $this->reply ? new CommentReplyResource($this->reply) : null,
        ];
    }
}
