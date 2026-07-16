<?php

declare(strict_types=1);

namespace App\Ai\Agents\MagicRoutes;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use RuntimeException;
use Stringable;

class HundredPercentGlutenFreeCountyContentAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /** @var Collection<int, Eatery> */
    protected Collection $eateries;

    public function __construct(protected EateryMagicRouteRecord $routeRecord)
    {
        throw_if(
            $this->routeRecord->resolver_type !== EateryMagicRouteType::HundredPercentGlutenFree,
            new RuntimeException('Magic route must be hundred percent gluten free'),
        );

        throw_if(
            ! $this->routeRecord->location instanceof EateryCounty,
            new RuntimeException('Magic route must be a for a county'),
        );

        $this->parseEateries();

        $this->routeRecord->location->loadMissing([
            'activeTowns' => fn (Relation $towns) => $towns->withCount(['liveEateries', 'liveBranches']),
        ]);
    }

    protected function parseEateries(): void
    {
        $pipeline = app(GetEateriesForMagicRoutePipeline::class);
        $pipeline->run($this->routeRecord->builder_config, []);

        $this->eateries = $pipeline->rawData()->hydrated;
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return view('prompts.magic-routes.hundred-percent-gluten-free.county', [
            'county' => $this->routeRecord->location,
            'eateries' => $this->eateries,
            'townGroups' => $this->eateriesGroupedByTown(),
        ])->render();
    }

    protected function eateriesGroupedByTown(): Collection
    {
        return $this->eateries->groupBy('town_id');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page_intro' => $schema->string()->required(),
            'meta_description' => $schema->string()->required(),
            'meta_keywords' => $schema->array()->items($schema->string())->required(),
            ...$this->eateriesGroupedByTown()
                ->mapWithKeys(fn (Collection $eateries) => [$eateries->first()->town?->slug => $schema->string()->required()]),
        ];
    }
}
