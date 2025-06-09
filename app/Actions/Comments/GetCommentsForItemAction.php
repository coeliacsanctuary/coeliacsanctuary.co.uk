<?php

declare(strict_types=1);

namespace App\Actions\Comments;

use App\Contracts\Comments\HasComments;
use App\ResourceCollections\Comments\CommentCollection;

class GetCommentsForItemAction
{
    /**
     * @template T of HasComments
     *
     * @param  T  $item
     * @return CommentCollection<T>
     */
    public function handle(HasComments $item): CommentCollection
    {
        return new CommentCollection(
            $item->comments()
                ->with('reply')
                ->simplePaginate(5, pageName: 'commentPage')
        );
    }
}
