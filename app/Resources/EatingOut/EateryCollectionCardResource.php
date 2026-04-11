<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/** @mixin EateryCollection */
class EateryCollectionCardResource extends JsonResource
{
    /** @return array{title: string|Stringable, link: string, image: string, date: string, description: string, eateries_count: int|null} */
    public function toArray(Request $request)
    {
        return [
            'title' => Str::of($this->title)->replace('&quot;', '"'),
            'link' => route('eating-out.collections.show', ['eateryCollection' => $this]),
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'date' => $this->published,
            'description' => $this->meta_description,
            'eateries_count' => $this->eateries_count,
        ];
    }
}
