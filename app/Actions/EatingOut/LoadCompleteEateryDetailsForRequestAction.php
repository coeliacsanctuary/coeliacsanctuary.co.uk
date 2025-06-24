<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryReviewImage;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Symfony\Component\HttpFoundation\Response;

class LoadCompleteEateryDetailsForRequestAction
{
    public function handle(
        Eatery $eatery,
        EateryCounty $county,
        EateryTown $town,
        NationwideBranch $nationwideBranch,
        string $pageType = 'eatery',
        bool $showAllReviews = false,
    ): void {
        if (in_array($pageType, ['nationwide', 'branch'])) {
            /** @var EateryCounty $county */
            $county = EateryCounty::withoutGlobalScopes()->firstWhere('county', 'Nationwide');

            /** @var EateryTown $town */
            $town = EateryTown::withoutGlobalScopes()->firstWhere('town', 'nationwide');
        }

        $county->load(['country']);
        $town->setRelation('county', $county);

        $eatery->setRelation('town', $town);
        $eatery->setRelation('county', $county);

        $eatery->load([
            'adminReview' => function (HasOne $builder) use ($pageType, $showAllReviews, $nationwideBranch) {
                /** @var HasOne<EateryReview, Eatery> $builder */
                return $builder
                    ->latest()
                    ->with(['branch'])
                    ->when(
                        $pageType === 'branch' && $showAllReviews !== true,
                        fn (Builder $builder) => $builder->where('nationwide_branch_id', $nationwideBranch->id),
                    );

            },
            'approvedReviewImages' => function (HasMany $builder) {
                /** @var HasMany<EateryReviewImage, Eatery> $builder */
                return $builder->whereRelation('review', 'admin_review', false);
            },
            'reviews' => function (HasMany $builder) use ($pageType, $showAllReviews, $nationwideBranch) {
                /** @var HasMany<EateryReview, Eatery> $builder */
                return $builder
                    ->latest()
                    ->where('admin_review', false)
                    ->with(['branch'])
                    ->when(
                        $pageType === 'branch' && $showAllReviews !== true,
                        fn (Builder $builder) => $builder->where('nationwide_branch_id', $nationwideBranch->id),
                    );
            },
            'adminReview.images', 'reviews.images', 'restaurants', 'features', 'openingTimes',
        ])
            ->loadCount(['reviews']);

        if ($pageType === 'nationwide') {
            $eatery->load([
                'nationwideBranches.eatery', 'nationwideBranches.area', 'nationwideBranches.area.town', 'nationwideBranches.town',
                'nationwideBranches.town.county', 'nationwideBranches.county', 'nationwideBranches.country'
            ]);
        }

        if ($pageType === 'branch') {
            abort_if($nationwideBranch->eatery->isNot($eatery), Response::HTTP_NOT_FOUND);

            $nationwideBranch->load(['county', 'town']);

            $eatery->setRelation('branch', $nationwideBranch);
        }

        if ($eatery->branch && $pageType === 'nationwide') {
            $eatery->branch = null; /** @phpstan-ignore-line */
        }
    }
}
