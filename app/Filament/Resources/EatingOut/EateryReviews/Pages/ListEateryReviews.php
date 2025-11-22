<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReviews\Pages;

use App\Filament\Resources\EatingOut\EateryReviews\EateryReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListEateryReviews extends ListRecords
{
    protected static string $resource = EateryReviewResource::class;
}
