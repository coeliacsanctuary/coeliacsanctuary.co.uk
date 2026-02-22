<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\GetEateriesInTownForAskSealiacPipeline;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListEateriesInTownTool extends BaseTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<'text'
        List gluten free places to eat out in a given town.

        Do not list all places like for like, just list some top picks, ALWAYS link to the town and guide the user to the town page for more detailed results.

        A little hint, you can send the sort option to the town page with a `sort` query string, eg `?sort=rating`. On town pages, alphabetical is the default.

        If someone asks for 100% gluten free places, make use of the `100-gluten-free` feature filter.

        Some guidelines on the data you will receive:
        - If the branch object is present, then the main eatery is a nationwide chain (eg McDonalds) and the branch object contains details on the specific branch close to the user search term, treat the address in the branch object as the address of the location, ignoring the address in the parent data set.
        - An eatery has a type, if the type is 'Attraction' then it might have individual restaurants/eateries within that attraction listed with in the restaurants key.
        - `info` contains the key info we have for this eatery
        - If the eatery is 100% GF (is_fully_gf) then this should be shouted about and made clear - as this is a special case.
        - If the eatery has an `reviews.admin_review `object, then this is a review by the Coeliac Sanctuary team
        - An array of user reviews can be found in reviews.user_reviews, not some eateries may not have any reviews.

        If the user sorts by rating, then emphasise the rating in the results - Note, not every eatery will have a rating, there may be cases where no eateries in your results have a rating.
        text;
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        $filters = [
            'categories' => count($request->array('type')) > 0 ? $request->array('type') : null,
            'venueTypes' => count($request->array('venueTypes')) > 0 ? $request->array('venueTypes') : null,
            'features' => count($request->array('features')) > 0 ? $request->array('features') : null,
        ];

        $town = EateryTown::query()->findOrFail($request->integer('town_id'));

        return app(GetEateriesInTownForAskSealiacPipeline::class)
            ->run($town, $filters, $request->string('sort', 'alphabetical')->toString())
            ->map(function (Eatery $eatery) {
                /** @var NationwideBranch | null $branch */
                $branch = $eatery->relationLoaded('branch') ? $eatery->branch : null;

                /** @var Collection<int, EateryReview> $reviews */
                $reviews = $eatery->reviews;

                /** @var Collection<int, EateryFeature> $features */
                $features = $eatery->features;

                /** @var EateryReview | null $adminReview */
                $adminReview = $eatery->adminReview;

                /** @var EateryOpeningTimes | null $eateryOpeningTimes */
                $eateryOpeningTimes = $eatery->openingTimes;

                if ($branch) {
                    $reviews = $eatery->reviews->where('nationwide_branch_id', $branch->id);
                }

                return [
                    'id' => $eatery->id,
                    'name' => $eatery->name,
                    'link' => $eatery->absoluteLink(),
                    'county' => [
                        'name' => $eatery->county?->county,
                        'link' => $eatery->county?->link(),
                    ],
                    'town' => [
                        'name' => $eatery->town?->town,
                        'link' => $eatery->town?->link(),
                    ],
                    'area' => $eatery->area ? [
                        'name' => $eatery->area->area,
                        'link' => $eatery->area->link(),
                    ] : null,
                    'venue_type' => $eatery->venueType?->venue_type,
                    'type' => $eatery->type?->name,
                    'cuisine' => $eatery->cuisine?->cuisine,
                    'website' => $eatery->website,
                    'menu' => $eatery->gf_menu_link,
                    'restaurants' => $eatery->restaurants->map(fn (EateryAttractionRestaurant $restaurant): array => [
                        'name' => $restaurant->restaurant_name,
                        'info' => $restaurant->info,
                    ]),
                    'is_fully_gf' => $eatery->features->where('feature', '100% Gluten Free')->isNotEmpty(),
                    'info' => $eatery->info,
                    'location' => [
                        'address' => collect(explode("\n", $eatery->address))
                            ->map(fn (string $line) => mb_trim($line))
                            ->join(', '),
                    ],
                    'phone' => $eatery->phone,
                    'reviews' => [
                        'number' => $reviews->count(),
                        'average' => $eatery->average_rating,
                        'expense' => $eatery->average_expense,
                        'admin_review' => $adminReview ? [
                            'published' => $adminReview->created_at,
                            'date_diff' => $adminReview->human_date,
                            'body' => $adminReview->review,
                            'rating' => (float) $adminReview->rating,
                            'expense' => $adminReview->price,
                            'food_rating' => $adminReview->food_rating,
                            'service_rating' => $adminReview->service_rating,
                            'branch_name' => $adminReview->branch_name,
                        ] : null,
                        'user_reviews' => $reviews->map(fn (EateryReview $review) => [
                            'published' => $review->created_at,
                            'date_diff' => $review->human_date,
                            'name' => $review->name,
                            'body' => $review->review,
                            'rating' => (float) $review->rating,
                            'expense' => $review->price,
                            'food_rating' => $review->food_rating,
                            'service_rating' => $review->service_rating,
                            'branch_name' => $review->branch ? $review->branch->name : $review->branch_name,
                        ]),
                        'ratings' => collect(range(5, 1))->map(fn ($rating) => [
                            'rating' => $rating,
                            'count' => $reviews->filter(fn (EateryReview $reviewItem) => (int) $reviewItem->rating === $rating)->count(),
                        ]),
                    ],
                    'features' => $features->map(fn (EateryFeature $feature) => $feature->feature),
                    'opening_times' => $eateryOpeningTimes ? [
                        'is_open_now' => $eateryOpeningTimes->is_open_now,
                        'today' => [
                            'opens' => $eateryOpeningTimes->opensAt(),
                            'closes' => $eateryOpeningTimes->closesAt(),
                        ],
                        'days' => $eateryOpeningTimes->opening_times_array,
                    ] : null,
                    'branch' => $branch ? $this->formatBranch($branch) : null,
                ];
            })
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'town_id' => $schema
                ->integer()
                ->required(),
            'sort' => $schema
                ->string()
                ->default('alphabetical')
                ->enum(['rating', 'alphabetical'])
                ->description('The sort order, default is alphabetical, the other option is by rating.'),
            'venueTypes' => $schema
                ->array()
                ->items($schema->string())
                ->description('any venue type slugs to filter on, eg pub, chip shop'),
            'type' => $schema
                ->array()
                ->items($schema->string())
                ->description('any type slugs to filter on, eg eatery, attraction, hotel'),
            'features' => $schema
                ->array()
                ->items($schema->string())
                ->description('any feature slugs to filter on, eg gluten free menu'),
        ];
    }

    protected function formatBranch(NationwideBranch $branch): array
    {
        return [
            'name' => $branch->name ?: $branch->eatery->name,
            'county' => [
                'name' => $branch->county?->county,
                'link' => $branch->county?->link(),
            ],
            'town' => [
                'name' => $branch->town?->town,
                'link' => $branch->town?->link(),
            ],
            'area' => $branch->area ? [
                'name' => $branch->area->area,
                'link' => $branch->area->link(),
            ] : null,
            'link' => $branch->absoluteLink(),
            'location' => [
                'address' => collect(explode("\n", $branch->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
            ],
        ];
    }
}
