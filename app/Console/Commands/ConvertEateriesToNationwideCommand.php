<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multisearch;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class ConvertEateriesToNationwideCommand extends Command
{
    protected $signature = 'coeliac:convert-eateries-to-nationwide';

    public function handle(): void
    {
        $selectedIds = multisearch(
            label: 'Search and select eateries to convert',
            options: fn (string $value) => Eatery::query()
                ->with(['town', 'county'])
                ->where('county_id', '!=', 1)
                ->where('closed_down', false)
                ->where(
                    fn (Builder $query) => $query
                        ->whereLike('name', "%{$value}%")
                        ->orWhereHas('town', fn (Builder $query) => $query->whereLike('town', "%{$value}%"))
                )
                ->limit(20)
                ->get()
                ->mapWithKeys(fn (Eatery $eatery) => [$eatery->id => "{$eatery->name} — {$eatery->town?->town}, {$eatery->county?->county}"])
                ->toArray(),
            required: true,
        );

        $eateries = Eatery::withoutGlobalScopes()
            ->with(['town', 'county', 'country', 'area', 'features', 'reviews'])
            ->whereIn('id', $selectedIds)
            ->get();

        /** @var Eatery $first */
        $first = $eateries->first();

        $name = text(label: 'Name', default: $first->name, required: true);
        $phone = text(label: 'Phone', default: $first->phone ?? '');
        $website = text(label: 'Website', default: $first->website ?? '');

        $venueTypeId = select(
            label: 'Venue type',
            options: EateryVenueType::query()->pluck('venue_type', 'id')->toArray(),
            default: $first->venue_type_id,
        );

        $cuisineId = select(
            label: 'Cuisine',
            options: EateryCuisine::query()->pluck('cuisine', 'id')->toArray(),
            default: $first->cuisine_id,
        );

        $info = textarea(label: 'Info', default: $first->info ?? '');

        $featureIds = multiselect(
            label: 'Select features',
            options: EateryFeature::query()->pluck('feature', 'id')->toArray(),
            default: $first->features->pluck('id')->toArray(),
        );

        if ( ! confirm('Proceed to create the nationwide eatery and branches?')) {
            return;
        }

        try {
            DB::beginTransaction();

            $baseEatery = Eatery::create([
                'name' => $name,
                'address' => '',
                'lat' => '',
                'lng' => '',
                'phone' => $phone ?: null,
                'website' => $website ?: null,
                'venue_type_id' => $venueTypeId,
                'cuisine_id' => $cuisineId,
                'info' => $info,
                'type_id' => $first->type_id,
                'country_id' => 1,
                'county_id' => 1,
                'town_id' => 529,
                'live' => 1,
            ]);

            $baseEatery->features()->sync($featureIds);

            $usedSlugs = $baseEatery->nationwideBranches()->pluck('slug')->toArray();

            $branchCount = 0;
            $reviewCount = 0;

            foreach ($eateries as $eatery) {
                $branchName = ($eatery->name === $baseEatery->name) ? '' : $eatery->name;

                if ($branchName === '') {
                    /** @var EateryTown $town */
                    $town = $eatery->town;

                    $slug = $town->slug;

                    if (in_array($slug, $usedSlugs)) {
                        $slug = $town->slug . '-' . Str::slug($eatery->eateryPostcode());
                    }
                } else {
                    $slug = $eatery->slug;
                }

                $usedSlugs[] = $slug;

                $branch = NationwideBranch::create([
                    'wheretoeat_id' => $baseEatery->id,
                    'name' => $branchName,
                    'slug' => $slug,
                    'town_id' => $eatery->town_id,
                    'area_id' => $eatery->area_id,
                    'county_id' => $eatery->county_id,
                    'country_id' => $eatery->country_id,
                    'address' => $eatery->address,
                    'lat' => $eatery->lat,
                    'lng' => $eatery->lng,
                    'live' => $eatery->live,
                    'created_at' => $eatery->created_at,
                    'updated_at' => $eatery->updated_at,
                ]);

                ++$branchCount;

                $reviewCount += EateryReview::withoutGlobalScopes()
                    ->where('wheretoeat_id', $eatery->id)
                    ->update([
                        'wheretoeat_id' => $baseEatery->id,
                        'nationwide_branch_id' => $branch->id,
                    ]);

                $eatery->update(['live' => 0]);
            }

            DB::commit();

            $this->components->info("Created nationwide eatery \"{$baseEatery->name}\" (ID: {$baseEatery->id})");
            $this->components->info("Created {$branchCount} " . str('branch')->plural($branchCount));
            $this->components->info("Migrated {$reviewCount} " . str('review')->plural($reviewCount));
            $this->components->info("Deactivated {$eateries->count()} original " . str('eatery')->plural($eateries->count()));
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
