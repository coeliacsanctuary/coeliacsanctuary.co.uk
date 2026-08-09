<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Contracts\RegexRouteFallbackResolver;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * @property string $title
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

    /** @return Attribute<non-falsy-string, never> */
    public function title(): Attribute
    {
        return Attribute::get(function () {
            $location = match($this->location::class) {
                EateryCounty::class => $this->location->county,
                EateryTown::class => $this->location->town,
                EateryArea::class => $this->location->area,
                default => throw new RuntimeException('Invalid location type'),
            };

            return match ($this->resolver_type) {
                EateryMagicRouteType::HundredPercentGlutenFree => "Eating 100% Gluten Free in {$location}",
            };
        });
    }

    public function link(): ?string
    {
        $resolver = $this->resolver_type->fallbackResolver();

        if ($resolver instanceof RegexRouteFallbackResolver) {
            $path = $resolver->generateRoutePath(['location' => $this->raw_location]);

            return Str::start($path, '/');
        }

        return null;
    }
}
