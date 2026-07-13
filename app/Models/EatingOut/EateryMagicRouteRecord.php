<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EateryMagicRouteRecord extends Model
{
    protected $table = 'wheretoeat_magic_route_records';

    protected $casts = [
        'resolver_type' => EateryMagicRouteType::class,
        'builder_config' => Configuration::class,
    ];

    /** @return MorphTo<EateryCounty|EateryTown|EateryArea, $this> */
    public function location(): MorphTo
    {
        return $this->morphTo('location');
    }
}
