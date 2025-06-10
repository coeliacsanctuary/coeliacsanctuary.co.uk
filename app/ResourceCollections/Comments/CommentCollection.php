<?php

declare(strict_types=1);

namespace App\ResourceCollections\Comments;

use App\Resources\Comments\CommentResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
{
    public $collects = CommentResource::class;
}
