<?php

declare(strict_types=1);

namespace App\Enums\EatingOut;

use App\Ai\Agents\MagicRoutes\HundredPercentGlutenFreeMagicRouteContentAgent;
use App\Ai\Agents\MagicRoutes\MagicRouteAgent;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;

enum EateryMagicRouteType: string
{
    case HundredPercentGlutenFree = 'hundred-percent-gluten-free';

    public function label(): string
    {
        return match ($this) {
            self::HundredPercentGlutenFree => 'Hundred Percent Gluten Free',
        };
    }

    public function builderConfiguration(): ?callable
    {
        return match ($this) {
            /** @phpstan-ignore-next-line */
            self::HundredPercentGlutenFree => fn (Configuration $configuration) => $configuration
                ->addJoin(new Join('wheretoeat_assigned_features', 'wheretoeat_assigned_features.wheretoeat_id', 'wheretoeat.id'))
                ->addWhere(new Where('wheretoeat_assigned_features.feature_id', '=', 1)), // 100 percent gluten free,
            default => null,
        };
    }

    public function agent(array $params = []): MagicRouteAgent
    {
        return match ($this) {
            self::HundredPercentGlutenFree => HundredPercentGlutenFreeMagicRouteContentAgent::make(...$params),
        };
    }
}
