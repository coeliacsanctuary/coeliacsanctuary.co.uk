<?php

declare(strict_types=1);

namespace App\Console\Commands\OneTime;

use App\Ai\Agents\TravelCardCountryScoringAgent;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\table;
use function Laravel\Prompts\title;
use function Laravel\Prompts\warning;

/** @phpstan-type TermScore array{term_id: int, show_on_product_page: bool, score: int|null, reason: string, language?: string} */
class ScoreTravelCardCountriesCommand extends Command
{
    protected $signature = 'coeliac:one-time:score-travel-card-countries';

    /** @var array<int, string> */
    protected array $skipped = [];

    protected int $termsMerged = 0;

    public function handle(): void
    {
        $this->mergeDuplicateTerms();

        $products = $this->travelCardProducts();

        $products->each(function (ShopProduct $product, int $index) use ($products): void {
            $position = $index + 1;
            $total = $products->count();

            title("{$position}/{$total} {$product->title}");

            $this->scoreProduct($product);
        });

        info("Duplicate search terms merged: {$this->termsMerged}");

        if ($this->skipped !== []) {
            warning('Skipped, the agent could not produce a valid ordering:');

            foreach ($this->skipped as $skipped) {
                note($skipped);
            }
        }
    }

    /** @return Collection<int, ShopProduct> */
    protected function travelCardProducts(): Collection
    {
        return ShopProduct::query()
            ->withoutGlobalScopes()
            ->whereHas('categories', fn (Builder $query) => $query->withoutGlobalScopes()->whereIn('shop_categories.id', [1, 11]))
            ->get();
    }

    protected function mergeDuplicateTerms(): void
    {
        TravelCardSearchTerm::query()
            ->get()
            ->groupBy(fn (TravelCardSearchTerm $term) => Str::lower($term->term) . '|' . $term->type)
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->each($this->mergeDuplicateGroup(...));
    }

    /** @param Collection<int, TravelCardSearchTerm> $group */
    protected function mergeDuplicateGroup(Collection $group): void
    {
        /** @var TravelCardSearchTerm $survivor */
        $survivor = $group->sortBy([
            fn (TravelCardSearchTerm $a, TravelCardSearchTerm $b) => $b->hits <=> $a->hits,
            fn (TravelCardSearchTerm $a, TravelCardSearchTerm $b) => $a->id <=> $b->id,
        ])->first();

        $group
            ->reject(fn (TravelCardSearchTerm $term) => $term->is($survivor))
            ->each(function (TravelCardSearchTerm $loser) use ($survivor): void {
                $this->mergeAssignments($loser, $survivor);

                $survivor->increment('hits', $loser->hits);

                $loser->delete();

                ++$this->termsMerged;

                note("Merged search term {$loser->id} ({$loser->term}) into {$survivor->id} ({$survivor->term})");
            });
    }

    protected function mergeAssignments(TravelCardSearchTerm $loser, TravelCardSearchTerm $survivor): void
    {
        DB::table('shop_product_assigned_travel_card_search_terms')
            ->where('search_term_id', $loser->id)
            ->get()
            ->each(function (object $assignment) use ($survivor): void {
                $existing = DB::table('shop_product_assigned_travel_card_search_terms')
                    ->where('search_term_id', $survivor->id)
                    ->where('product_id', $assignment->product_id)
                    ->first();

                DB::table('shop_product_assigned_travel_card_search_terms')->updateOrInsert(
                    ['search_term_id' => $survivor->id, 'product_id' => $assignment->product_id],
                    [
                        'card_show_on_product_page' => ($existing?->card_show_on_product_page ?? false) || $assignment->card_show_on_product_page, /** @phpstan-ignore-line */
                        'card_language' => $existing?->card_language ?? $assignment->card_language, /** @phpstan-ignore-line */
                        'card_score' => $existing?->card_score ?? $assignment->card_score, /** @phpstan-ignore-line */
                    ]
                );
            });

        DB::table('shop_product_assigned_travel_card_search_terms')
            ->where('search_term_id', $loser->id)
            ->delete();
    }

    protected function scoreProduct(ShopProduct $product): void
    {
        $product->load([
            'travelCardSearchTerms' => fn (Relation $query) => $query
                ->where('type', 'country') /** @phpstan-ignore-line */
                ->where('shop_product_assigned_travel_card_search_terms.card_show_on_product_page', true),
        ]);

        if ($product->travelCardSearchTerms->isEmpty()) {
            note('No countries assigned, skipping');

            return;
        }

        $agent = new TravelCardCountryScoringAgent($product);

        $scores = $this->askAgent($agent) ?? $this->askAgent($agent);

        if ($scores === null) {
            $this->skipped[] = $product->title;

            warning('No valid ordering returned');

            return;
        }

        $this->writeScores($product, $agent, $scores);

        $this->printScores($product, $agent, $scores);
    }

    /** @return Collection<int, TermScore>|null */
    protected function askAgent(TravelCardCountryScoringAgent $agent): ?Collection
    {
        try {
            /** @var StructuredAgentResponse $response */
            $response = $agent->prompt('Score the countries assigned to this travel card.');

            /** @var array<int, TermScore> $terms */
            $terms = $response['terms'];

            $scores = collect($terms);

            return $this->isValid($agent, $scores) ? $scores : null;
        } catch (Throwable $e) {
            warning($e->getMessage());

            return null;
        }
    }

    /** @param Collection<int, TermScore> $scores */
    protected function isValid(TravelCardCountryScoringAgent $agent, Collection $scores): bool
    {
        $lists = $this->groupByLanguage($agent, $scores);

        if ($lists->isEmpty()) {
            return false;
        }

        return $lists->every(function (Collection $list) {
            $values = $list->pluck('score');

            if ($values->contains(null)) {
                return false;
            }

            return $values->unique()->count() === $values->count()
                && $values->max() === 100
                && $values->min() >= 0;
        });
    }

    /** @param TermScore $score */
    protected function isKept(array $score): bool
    {
        return $score['show_on_product_page'] === true;
    }

    /** @param TermScore $score */
    protected function belongsToLanguage(array $score, string $language): bool
    {
        return in_array($score['language'] ?? null, [$language, 'both'], true);
    }

    /**
     * @param  Collection<int, TermScore> $scores
     * @return Collection<string, Collection<int, TermScore>>
     */
    protected function groupByLanguage(TravelCardCountryScoringAgent $agent, Collection $scores): Collection
    {
        $kept = $scores->filter($this->isKept(...));

        if ($agent->isSingleLanguage()) {
            return $kept->isEmpty() ? collect() : collect(['' => $kept->values()]);
        }

        return collect($agent->languages())
            ->mapWithKeys(fn (string $language) => [
                $language => $kept
                    ->filter(fn (array $score) => $this->belongsToLanguage($score, $language))
                    ->sortByDesc('score')
                    ->values(),
            ])
            ->filter(fn (Collection $list) => $list->isNotEmpty());
    }

    /** @param Collection<int, TermScore> $scores */
    protected function writeScores(ShopProduct $product, TravelCardCountryScoringAgent $agent, Collection $scores): void
    {
        $scores->each(function (array $score) use ($product, $agent): void {
            $keep = $this->isKept($score);

            $pivot = [
                'card_show_on_product_page' => $keep,
                'card_score' => $keep ? $score['score'] : null,
            ];

            if ( ! $agent->isSingleLanguage()) {
                $pivot['card_language'] = $keep ? ($score['language'] ?? null) : null;
            }

            $product->travelCardSearchTerms()->updateExistingPivot($score['term_id'], $pivot);
        });
    }

    /** @param Collection<int, TermScore> $scores */
    protected function printScores(ShopProduct $product, TravelCardCountryScoringAgent $agent, Collection $scores): void
    {
        $terms = $product->travelCardSearchTerms->pluck('term', 'id');

        $this->groupByLanguage($agent, $scores)->each(function (Collection $list, string $language) use ($terms): void {
            if ($language !== '') {
                note($language);
            }

            table(
                ['Country', 'Score', 'Flag', 'Reason'],
                $list->map(function (array $score) use ($terms) {
                    $term = $this->termName($terms, $score);

                    return [
                        Str::title($term),
                        $score['score'],
                        Helpers::countryCode($term) ?? '-',
                        $score['reason'],
                    ];
                })->toArray(),
            );
        });

        $scores
            ->reject($this->isKept(...))
            ->each(function (array $score) use ($terms): void {
                $term = $this->termName($terms, $score);

                note("Hidden: {$term} — {$score['reason']}");
            });
    }

    /**
     * @param Collection<int, string>  $terms
     * @param TermScore $score
     */
    protected function termName(Collection $terms, array $score): string
    {
        return (string) $terms->get($score['term_id'], '?');
    }
}
