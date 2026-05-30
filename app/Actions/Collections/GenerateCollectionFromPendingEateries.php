<?php

declare(strict_types=1);

namespace App\Actions\Collections;

use App\DataObjects\EatingOut\PendingEatery;
use App\Models\Collections\Collection as CollectionModel;
use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionGroupItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class GenerateCollectionFromPendingEateries
{
    /** @param Collection<int, PendingEatery> $pendingEateries */
    public function handle(Collection $pendingEateries, string $name, string $orderField): CollectionModel
    {
        return DB::transaction(function () use ($pendingEateries, $name, $orderField): CollectionModel {
            $collection = CollectionModel::query()->create([
                'title' => $name,
                'slug' => Str::slug($name),
                'meta_keywords' => '',
                'meta_description' => '',
                'long_description' => '',
                'body' => '',
                'live' => false,
                'draft' => true,
                'publish_at' => null,
            ]);

            $fieldIdMap = $this->buildFieldIdMap($pendingEateries, $orderField);
            $groupNameMap = $this->buildGroupNameMap($fieldIdMap, $orderField);
            $grouped = $this->groupPendingEateries($pendingEateries, $fieldIdMap, $groupNameMap);

            foreach ($grouped as $groupName => $groupItems) {
                $group = CollectionGroup::query()->create([
                    'collection_id' => $collection->id,
                    'title' => $groupName,
                    'body' => null,
                ]);

                foreach ($groupItems as $pendingEatery) {
                    CollectionGroupItem::query()->create([
                        'collection_group_id' => $group->id,
                        'item_id' => $pendingEatery->branchId ?? $pendingEatery->id,
                        'item_type' => $pendingEatery->branchId ? NationwideBranch::class : Eatery::class,
                        'item_title' => null,
                        'item_description' => null,
                    ]);
                }
            }

            return $collection;
        });
    }

    /**
     * @param Collection<int, PendingEatery> $pendingEateries
     * @return array<string, int>
     */
    protected function buildFieldIdMap(Collection $pendingEateries, string $orderField): array
    {
        $fieldIdMap = [];

        $eateryIds = $pendingEateries->filter(fn (PendingEatery $pending) => $pending->branchId === null)->pluck('id');
        $branchIds = $pendingEateries->filter(fn (PendingEatery $pending) => $pending->branchId !== null)->pluck('branchId');

        if ($eateryIds->isNotEmpty()) {
            Eatery::query()
                ->select(['id', "{$orderField}_id"])
                ->whereIn('id', $eateryIds)
                ->get()
                ->each(function (Eatery $eatery) use (&$fieldIdMap, $orderField): void {
                    $fieldIdMap["e_{$eatery->id}"] = $eatery->{"{$orderField}_id"};
                });
        }

        if ($branchIds->isNotEmpty()) {
            NationwideBranch::query()
                ->select(['id', "{$orderField}_id"])
                ->whereIn('id', $branchIds)
                ->get()
                ->each(function (NationwideBranch $branch) use (&$fieldIdMap, $orderField): void {
                    $fieldIdMap["b_{$branch->id}"] = $branch->{"{$orderField}_id"};
                });
        }

        return $fieldIdMap;
    }

    /**
     * @param array<string, int> $fieldIdMap
     * @return array<int, string>
     */
    protected function buildGroupNameMap(array $fieldIdMap, string $orderField): array
    {
        $uniqueFieldIds = collect($fieldIdMap)->unique()->filter()->values();

        return match ($orderField) {
            'country' => EateryCountry::query()
                ->whereIn('id', $uniqueFieldIds)
                ->get()
                ->mapWithKeys(fn (EateryCountry $country) => [$country->id => $country->country])
                ->all(),

            'county' => EateryCounty::query()
                ->whereIn('id', $uniqueFieldIds)
                ->with('country')
                ->get()
                ->mapWithKeys(function (EateryCounty $county) {
                    /** @var EateryCountry $country */
                    $country = $county->country;

                    return [$county->id => "{$county->county}, {$country->country}"];
                })
                ->all(),

            'town' => EateryTown::query()
                ->whereIn('id', $uniqueFieldIds)
                ->with(['county', 'county.country'])
                ->get()
                ->mapWithKeys(function (EateryTown $town) {
                    /** @var EateryCounty $county */
                    $county = $town->county;

                    /** @var EateryCountry $country */
                    $country = $county->country;

                    return [$town->id => "{$town->town}, {$county->county}, {$country->country}"];
                })
                ->all(),

            'area' => EateryArea::query()
                ->whereIn('id', $uniqueFieldIds)
                ->with(['town', 'town.county', 'town.county.country'])
                ->get()
                ->mapWithKeys(function (EateryArea $area) {
                    /** @var EateryTown $town */
                    $town = $area->town;

                    /** @var EateryCounty $county */
                    $county = $town->county;

                    /** @var EateryCountry $country */
                    $country = $county->country;

                    return [$area->id => "{$area->area}, {$town->town}, {$county->county}, {$country->country}"];
                })
                ->all(),

            default => throw new InvalidArgumentException("Unsupported order field: {$orderField}"),
        };
    }

    /**
     * @param Collection<int, PendingEatery> $pendingEateries
     * @param array<string, int> $fieldIdMap
     * @param array<int, string> $groupNameMap
     * @return array<string, list<PendingEatery>>
     */
    protected function groupPendingEateries(Collection $pendingEateries, array $fieldIdMap, array $groupNameMap): array
    {
        $grouped = [];

        foreach ($pendingEateries as $pendingEatery) {
            $key = $pendingEatery->branchId ? "b_{$pendingEatery->branchId}" : "e_{$pendingEatery->id}";
            $fieldId = $fieldIdMap[$key] ?? null;
            $groupName = $fieldId !== null ? ($groupNameMap[$fieldId] ?? 'Unknown') : 'Unknown';

            $grouped[$groupName] ??= [];
            $grouped[$groupName][] = $pendingEatery;
        }

        return $grouped;
    }
}
