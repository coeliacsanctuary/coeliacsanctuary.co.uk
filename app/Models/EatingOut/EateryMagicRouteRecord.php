<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property EateryMagicRouteType $resolver_type
 * @property array $body
 * @property Configuration $builder_config
 * @property EateryCounty|EateryTown|EateryArea $location
 */
class EateryMagicRouteRecord extends Model
{
    protected $table = 'wheretoeat_magic_route_records';

    protected $casts = [
        'body' => 'array',
        'resolver_type' => EateryMagicRouteType::class,
        'builder_config' => Configuration::class,
    ];

    /** @return MorphTo<EateryCounty|EateryTown|EateryArea, $this> */
    public function location(): MorphTo
    {
        /** @var MorphTo<EateryCounty|EateryTown|EateryArea, $this> $relation */
        $relation = $this->morphTo('location');

        return $relation;
    }
}
