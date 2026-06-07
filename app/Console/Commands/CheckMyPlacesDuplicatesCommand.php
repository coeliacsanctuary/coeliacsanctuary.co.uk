<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

use function Laravel\Prompts\confirm;

class CheckMyPlacesDuplicatesCommand extends Command
{
    protected $signature = 'coeliac:eating-out:check-my-places-duplicates';

    protected $description = 'Check My Places recommendations against the eateries table and interactively delete duplicates';

    public function handle(): void
    {
        $recommendations = EateryRecommendation::query()
            ->where('email', 'alisondwheatley@gmail.com')
            ->where('completed', false)
            ->where('ignored', false)
            ->get();

        $this->components->info("Scanning {$recommendations->count()} recommendations...");

        $confirmed = collect();
        $possible = collect();

        foreach ($recommendations as $recommendation) {
            $matches = Eatery::withoutGlobalScopes()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($recommendation->place_name)])
                ->with(['town', 'county'])
                ->get();

            if ($matches->isEmpty()) {
                continue;
            }

            $locationWords = $this->parseLocationWords($recommendation->place_location ?? '');
            $locationMatch = $matches->first(fn (Eatery $eatery) => $this->locationMatches($eatery, $locationWords));

            if ($locationMatch) {
                $confirmed->push(['recommendation' => $recommendation, 'eatery' => $locationMatch]);
            } else {
                $possible->push(['recommendation' => $recommendation, 'eateries' => $matches]);
            }
        }

        $deleted = 0;

        if ($confirmed->isNotEmpty()) {
            $this->components->info("Found {$confirmed->count()} confirmed matches (name + location agree):");
            $this->newLine();

            $this->table(
                ['Recommendation', 'Place Location', 'Matched Eatery', 'Eatery Town'],
                $confirmed->map(fn (array $item) => [
                    $item['recommendation']->place_name,
                    $item['recommendation']->place_location,
                    $item['eatery']->name,
                    /** @phpstan-ignore-next-line */
                    $item['eatery']->town?->town ?? '—',
                ])->toArray(),
            );

            if (confirm("Delete these {$confirmed->count()} confirmed matches?")) {
                $confirmed->each(fn (array $item) => $item['recommendation']->delete());
                $deleted += $confirmed->count();
                $this->components->info("Deleted {$confirmed->count()} confirmed matches.");
            }
        } else {
            $this->components->info('No confirmed matches found.');
        }

        $this->newLine();

        if ($possible->isNotEmpty()) {
            $this->components->info("{$possible->count()} possible matches found (name matches but location unclear).");

            if (confirm("Review the {$possible->count()} possible matches one by one?")) {
                foreach ($possible as $item) {
                    /** @var EateryRecommendation $recommendation */
                    $recommendation = $item['recommendation'];
                    /** @var Collection<int, Eatery> $eateries */
                    $eateries = $item['eateries'];

                    $this->newLine();
                    $this->components->twoColumnDetail(
                        '<fg=yellow;options=bold>' . $recommendation->place_name . '</>',
                        $recommendation->place_location ?? '',
                    );

                    if ($recommendation->place_details) {
                        $this->line('  ' . str($recommendation->place_details)->limit(120));
                    }

                    $this->newLine();
                    $this->line('  <fg=gray>Already in the guide:</>');

                    foreach ($eateries as $eatery) {
                        $location = implode(', ', array_filter([$eatery->town?->town, $eatery->county?->county]));

                        $this->components->twoColumnDetail("  {$eatery->name}", $location);
                    }

                    $this->newLine();

                    if (confirm("Delete '{$recommendation->place_name}' ({$recommendation->place_location})?", default: false)) {
                        $recommendation->delete();
                        $deleted++;
                    }
                }
            }
        } else {
            $this->components->info('No possible matches to review.');
        }

        $this->newLine();
        $this->components->info("Done. Deleted {$deleted} record(s) in total.");
    }

    /** @return Collection<int, string> */
    protected function parseLocationWords(string $location): Collection
    {
        /** @var list<string> $parts */
        $parts = preg_split('/[\s,\-\/]+/', $location) ?: [];

        /** @phpstan-ignore-next-line */
        return collect($parts)
            ->map(fn (string $word) => (string) mb_strtolower(mb_trim($word)))
            ->filter(fn (string $word) => mb_strlen($word) >= 3)
            ->unique()
            ->values();
    }

    /** @param Collection<int, string> $locationWords */
    protected function locationMatches(Eatery $eatery, Collection $locationWords): bool
    {
        $town = mb_strtolower($eatery->town?->town ?? ''); // @phpstan-ignore nullsafe.neverNull
        $county = mb_strtolower($eatery->county?->county ?? ''); // @phpstan-ignore nullsafe.neverNull

        return $locationWords->contains(
            fn (string $word) => (mb_strlen($town) >= 3 && (str_contains($town, $word) || str_contains($word, $town)))
                || (mb_strlen($county) >= 3 && (str_contains($county, $word) || str_contains($word, $county)))
        );
    }
}
