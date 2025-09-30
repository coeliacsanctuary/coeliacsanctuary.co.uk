<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Geocoder\Facades\Geocoder;

class WteNationwideImport implements ToCollection, WithHeadingRow
{
    use Importable;

    /**
     * @param Collection<int, Collection<int, array>> $collection
     * @return Collection<int, Collection<int|string, mixed>>
     */
    public function collection(Collection $collection): Collection
    {
        return $collection->map(function (Collection $item) {
            $result = $this->buildBaseObject($item);

            try {
                $this->findCountry($result);
                $this->findCounty($result);
                $this->findTown($result);
                $this->findArea($result);

                $this->findLatLng($result);
            } catch (Exception $exception) {
                $result['error'] = true;
                $result['message'] = $exception->getMessage();
            }

            return collect($result);
        });
    }

    protected function findLatLng(array &$item): void
    {
        $search = $item['name'] . ', ' . $item['address']['raw'];

        $result = Geocoder::getCoordinatesForAddress($search);

        $item['lat'] = $result['lat'];
        $item['lng'] = $result['lng'];
    }

    protected function findCountry(array &$item): void
    {
        $countryName = $item['country']['name'];

        /** @var EateryCountry | null $country */
        $country = EateryCountry::withoutGlobalScopes()->where('country', $countryName)->first();

        if ( ! $country) {
            throw new Exception("Can't find country - {$countryName})");
        }

        $item['country'] = [
            'id' => $country->id,
            'name' => $country->country,
        ];
    }

    protected function findCounty(array &$item): void
    {
        $countyName = $item['county']['name'];

        /** @var EateryCounty | null $county */
        $county = EateryCounty::withoutGlobalScopes()->where('county', $countyName)->first();

        if ( ! $county && Str::contains($countyName, '-')) {
            $countyName = str_replace('-', ' ', $countyName);

            $county = EateryCounty::withoutGlobalScopes()->where('county', $countyName)->first();
        }

        if ( ! $county) {
            /** @var EateryCountry | null $country */
            $country = EateryCountry::withoutGlobalScopes()->where('country', $item['country']['name'])->first();

            if ($country) {
                $county = $country->counties()->make(['county' => $countyName]);
            }
        }

        if ( ! $county) {
            throw new Exception("Can't find county - {$countyName})");
        }

        $item['county'] = [
            'id' => $county->exists ? $county->id : 'NEW',
            'name' => $county->county,
        ];
    }

    protected function findTown(array &$item): void
    {
        $townName = $item['town']['name'];

        /** @var EateryTown | null $town */
        $town = EateryTown::withoutGlobalScopes()->where('town', $townName)->first();

        if ( ! $town && Str::contains($townName, '-')) {
            $townName = str_replace('-', ' ', $townName);

            $town = EateryTown::withoutGlobalScopes()->where('town', $townName)->first();
        }

        if ( ! $town) {
            /** @var EateryCounty | null $county */
            $county = EateryCounty::withoutGlobalScopes()->where('county', $item['county']['name'])->first();

            if ($county) {
                $town = $county->towns()->make(['town' => $townName]);
            }
        }

        if ( ! $town) {
            throw new Exception("Can't find town - {$townName})");
        }

        $item['town'] = [
            'id' => $town->exists ? $town->id : 'NEW',
            'name' => $town->town,
        ];
    }

    protected function findArea(array &$item): void
    {
        $areaName = $item['area']['name'];

        if ( ! $areaName) {
            return;
        }

        /** @var EateryArea | null $area */
        $area = EateryArea::withoutGlobalScopes()->where('area', $areaName)->first();

        if ( ! $area && Str::contains($areaName, '-')) {
            $areaName = str_replace('-', ' ', $areaName);

            $area = EateryArea::withoutGlobalScopes()->where('area', $areaName)->first();
        }

        if ( ! $area) {
            /** @var EateryTown | null $borough */
            $borough = EateryTown::withoutGlobalScopes()->where('town', $item['town']['name'])->first();

            if ($borough) {
                $area = $borough->areas()->make(['area' => $areaName]);
            }
        }

        if ( ! $area) {
            throw new Exception("Can't find area - {$areaName})");
        }

        $item['area'] = [
            'id' => $area->exists ? $area->id : 'NEW',
            'name' => $area->area,
        ];
    }

    /**
     * @param Collection<int, array> $item
     * @return array<string, mixed>
     */
    protected function buildBaseObject(Collection $item): array
    {
        return [
            'error' => false,
            'message' => '',
            'wheretoeat_id' => $item->get('wheretoeat_id'),
            'name' => $item->get('name'),
            'country' => [
                'id' => '',
                'name' => $item->get('country'),
            ],
            'county' => [
                'id' => '',
                'name' => $item->get('county'),
            ],
            'town' => [
                'id' => '',
                'name' => $item->get('town'),
            ],
            'area' => [
                'id' => null,
                'name' => $item->get('area'),
            ],
            'address' => [
                'raw' => $item->get('address'),
                'formatted' => str_replace(', ', "\n", $item->get('address', '')),
                /** @phpstan-ignore-next-line  */
                'bits' => explode(', ', $item->get('address', '')),
            ],
            'lat' => '',
            'lng' => '',
            'live' => $item->get('live', true),
        ];
    }
}
