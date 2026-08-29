<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Facades\Cache;

class GetEateryGuideStatisticsAction
{
    /** @return array{total: int, eateries: int, attractions: int, hotels: int, branches: int, reviews: int} */
    public function handle(): array
    {
        /** @var string $key */
        $key = config('coeliac.cacheable.eating-out.guide-statistics');

        /** @var array{total: int, eateries: int, attractions: int, hotels: int, branches: int, reviews: int} $statistics */
        $statistics = Cache::rememberForever($key, function (): array {
            $countOfType = fn (EateryType $type): int => Eatery::query()
                ->where('type_id', $type->value)
                ->count();

            $eateries = $countOfType(EateryType::EATERY);
            $attractions = $countOfType(EateryType::ATTRACTION);
            $hotels = $countOfType(EateryType::HOTEL);
            $branches = NationwideBranch::query()->count();

            return [
                'total' => $eateries + $attractions + $hotels + $branches,
                'eateries' => $eateries,
                'attractions' => $attractions,
                'hotels' => $hotels,
                'branches' => $branches,
                'reviews' => EateryReview::query()->count(),
            ];
        });

        return $statistics;
    }
}
