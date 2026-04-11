<?php

declare(strict_types=1);

namespace App\Ai\Tools\AskSealiac;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForAskSealiacSearchPipeline;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchEateriesBySearchTermTool extends BaseTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<'text'
        Search for gluten free places to eat out for a given search term, a search term could be a place name, a postcode, a street etc.

        The tool can be triggered with just the search term, assume the defaults for radius and sort. Give results, but state they can be set.

        Do not list all places like for like, just list some top picks, if the search results are all, or mostly in one town, the closest results, then ALWAYS link to the town and guide the user to the town page for more detailed results.

        A little hint, you can send the sort option to the town page with a `sort` query string, eg `?sort=rating`. On town pages, alphabetical is the default.

        If someone asks for 100% gluten free places, make use of the `100-gluten-free` feature filter.

        Some guidelines on the data you will receive:
        - All distances are as the crow flies, rather than the route.
        - If the branch object is present, then the main eatery is a nationwide chain (eg McDonalds) and the branch object contains details on the specific branch close to the user search term, treat the address in the branch object as the address of the location, ignoring the address in the parent data set.
        - County is the UK county that eatery is in, eg Dorset, a link to that counties page is included in the dataset.
        - Town can either be a traditional UK town, in the listed county OR a London Borough (eg City of Westminster) IF the county is London. A link to the town specific page is included in the dataset.
        - If the County is London, then the eatery will also have an area, eg 'Leicester Square', if it is not a London eatery, then area will be null.
        - An eatery has a type, if the type is 'Attraction' then it might have individual restaurants/eateries within that attraction listed with in the restaurants key.
        - `info` contains the key info we have for this eatery
        - If the eatery is 100% GF (is_fully_gf) then this should be shouted about and made clear - as this is a special case.

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

        return app(GetEateriesForAskSealiacSearchPipeline::class)
            ->run($request->string('term')->toString(), $request->integer('radius', 5), $filters, $request->string('sort', 'distance')->toString())
            ->map(function (Eatery $eatery) {
                /** @var NationwideBranch | null $branch */
                $branch = $eatery->relationLoaded('branch') ? $eatery->branch : null;

                /** @var EateryVenueType $venueType */
                $venueType = $eatery->venueType;

                /** @var EateryType $eateryType */
                $eateryType = $eatery->type;

                if ($branch) {
                    /** @phpstan-ignore-next-line  */
                    $eatery->reviews = $eatery->reviews->where('nationwide_branch_id', $branch->id);
                }

                return [
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
                    'area' => [
                        'name' => $eatery->area?->area,
                        'link' => $eatery->area?->link(),
                    ],
                    'branch' => $branch ? [
                        'name' => $branch->name,
                        'location' => [
                            'address' => collect(explode("\n", $branch->address))
                                ->map(fn (string $line) => mb_trim($line))
                                ->join(', '),
                        ],
                        'link' => $branch->link(),
                    ] : null,
                    'venue_type' => $venueType->venue_type,
                    'type' => $eateryType->name,
                    'cuisine' => $eatery->cuisine?->cuisine,
                    'website' => $eatery->website,
                    'restaurants' => $eatery->restaurants->map(fn (EateryAttractionRestaurant $restaurant): array => [
                        'name' => $restaurant->restaurant_name,
                        'info' => $restaurant->info,
                    ]),
                    'info' => $eatery->info,
                    'is_fully_gf' => $eatery->features->where('feature', '100% Gluten Free')->isNotEmpty(),
                    'location' => [
                        'address' => collect(explode("\n", $eatery->address))
                            ->map(fn (string $line) => mb_trim($line))
                            ->join(', '),
                    ],
                    'phone' => $eatery->phone,
                    'reviews' => [
                        'number' => $eatery->reviews->count(),
                        'average' => $eatery->average_rating,
                    ],
                    'distance' => $branch->distance ?? $eatery->distance,
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
            'term' => $schema
                ->string()
                ->required(),
            'radius' => $schema
                ->integer()
                ->default(5)
                ->max(20)
                ->min(1)
                ->description('The radius, in miles, the default is 5, the max is 20, and min is 1.'),
            'sort' => $schema
                ->string()
                ->default('distance')
                ->enum(['distance', 'rating', 'alphabetical'])
                ->description('The sort order, default is distance, other options are by highest user rating, or alphabetically.'),
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
}
