<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryMagicRouteRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

/** @mixin EateryMagicRouteRecord */
class MagicRouteGuideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'link' => $this->link(),
            'description' => Arr::get($this->body, 'meta_description'),
        ];
    }
}
