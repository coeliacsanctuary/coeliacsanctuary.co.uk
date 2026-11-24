<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\EateryReviews\EateryReviewResource;
use Filament\Resources\RelationManagers\RelationManager;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $relatedResource = EateryReviewResource::class;

    public function isReadOnly(): bool
    {
        return false;
    }
}
