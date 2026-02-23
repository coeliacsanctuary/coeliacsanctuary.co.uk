<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class CountyEateryResource extends JsonResource
{
    /** @return array{name: string, link: string, town: array{name: string, link: string}, rating: int | null, rating_count: int | null, info: string, address: string} */
    public function toArray(Request $request)
    {
        /** @var EateryTown $town */
        $town = $this->town;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'link' => $this->link(),
            'town' => [
                'name' => $town->town,
                'link' => $town->link(),
            ],
            'rating' => $this->rating,
            'rating_count' => $this->rating_count,
            'info' => (string) $this->info,
            'address' => collect(explode("\n", $this->address))
                ->map(fn (string $line) => mb_trim($line))
                ->join(', '),
        ];
    }
}
