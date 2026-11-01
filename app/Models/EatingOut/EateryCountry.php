<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property string $image
 */
class EateryCountry extends Model
{
    protected $table = 'wheretoeat_countries';

    /** @return HasMany<Eatery, $this> */
    public function eateries(): HasMany
    {
        return $this->hasMany(Eatery::class, 'country_id');
    }

    /** @return HasMany<EateryCounty, $this> */
    public function counties(): HasMany
    {
        return $this->hasMany(EateryCounty::class, 'country_id');
    }

    /** @return Attribute<string, never> */
    public function image(): Attribute
    {
        $filename = Str::slug($this->country);

        return Attribute::get(fn (): string => asset("images/eating-out/{$filename}.jpg"));
    }
}
