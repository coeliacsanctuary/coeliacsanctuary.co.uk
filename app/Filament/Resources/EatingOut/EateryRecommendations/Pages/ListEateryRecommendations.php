<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Pages;

use App\Filament\Resources\EatingOut\EateryRecommendations\EateryRecommendationResource;
use Filament\Resources\Pages\ListRecords;

class ListEateryRecommendations extends ListRecords
{
    protected static string $resource = EateryRecommendationResource::class;
}
