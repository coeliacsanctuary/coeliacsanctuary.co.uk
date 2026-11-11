<?php

declare(strict_types=1);

namespace App\Support\Shop\ReviewInvitations;

use App\DataObjects\Shop\ReviewInvitationRule;
use App\Enums\Shop\PostageArea;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SendingRules
{
    /** @return Collection<int, ReviewInvitationRule> */
    public static function get(): Collection
    {
        return collect([
            new ReviewInvitationRule(
                Carbon::now()->subDays(5)->toImmutable(),
                fn (Builder $builder) => $builder->where('is_digital_only', true),
                '5 days',
            ),
            new ReviewInvitationRule(
                Carbon::now()->subDays(10)->toImmutable(),
                fn (Builder $builder) => $builder->whereRelation('postageCountry', 'postage_area_id', PostageArea::UK->value),
                '10 days',
            ),
            new ReviewInvitationRule(
                Carbon::now()->subWeeks(2)->toImmutable(),
                fn (Builder $builder) => $builder->whereRelation('postageCountry', 'postage_area_id', PostageArea::EUROPE->value),
                '2 weeks',
            ),
            new ReviewInvitationRule(
                Carbon::now()->subWeeks(3)->toImmutable(),
                fn (Builder $builder) => $builder->whereRelation('postageCountry', fn (Builder $relation) => $relation->whereIn('postage_area_id', [PostageArea::AMERICA->value, PostageArea::OCEANA->value])),
                '3 weeks'
            ),
        ]);
    }
}
