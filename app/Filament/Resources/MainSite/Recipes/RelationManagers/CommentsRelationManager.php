<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\RelationManagers;

use App\Filament\Resources\MainSite\Comments\CommentResource;
use Filament\Resources\RelationManagers\RelationManager;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $relatedResource = CommentResource::class;
}
