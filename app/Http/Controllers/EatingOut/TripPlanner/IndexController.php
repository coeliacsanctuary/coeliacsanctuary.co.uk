<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\TripPlanner;

use App\Http\Response\Inertia;
use Inertia\Response;

class IndexController
{
    public function __invoke(Inertia $inertia): Response
    {
        return $inertia
            ->title('Gluten Free Trip Planner')
            ->metaDescription('Plan your journey and find places to eat that cater to Coeliac and gluten free diets along the way')
            ->metaTags(['gluten free trip planner', 'gluten free road trip', 'gluten free places to eat', 'eating out uk'])
            ->render('EatingOut/TripPlanner');
    }
}
