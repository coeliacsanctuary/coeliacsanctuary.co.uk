<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Signature;
use App\DataObjects\Shop\ReviewInvitationRule;
use App\Enums\Shop\OrderState;
use App\Jobs\Shop\SendReviewInvitationJob;
use App\Models\Shop\ShopOrder;
use App\Support\Shop\ReviewInvitations\SendingRules;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('coeliac:prepare-shop-review-invitations')]
class PrepareShopReviewInvitationsCommand extends Command
{
    public function handle(): void
    {
        SendingRules::get()->each(fn (ReviewInvitationRule $rule) => ShopOrder::query()
            ->where('state_id', OrderState::SHIPPED)
            ->where('shipped_at', '<=', $rule->minimumDate)
            ->where('shipped_at', '>=', $rule->minimumDate->startOfDay())
            ->pipe(fn (Builder $query): Builder => call_user_func($rule->pipe, $query))
            ->whereDoesntHave('reviewInvitation')
            ->whereDoesntHave('refunds')
            ->get()
            ->each(fn (ShopOrder $order) => SendReviewInvitationJob::dispatch($order, $rule->notificationDelayText)));
    }
}
