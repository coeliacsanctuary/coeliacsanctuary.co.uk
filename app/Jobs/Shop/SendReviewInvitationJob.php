<?php

declare(strict_types=1);

namespace App\Jobs\Shop;

use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\ReviewOrderInvitationNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReviewInvitationJob implements ShouldQueue
{
    use \Illuminate\Foundation\Queue\Queueable;

    public function __construct(protected ShopOrder $order, protected string $delayText)
    {
    }

    public function handle(): void
    {
        $invitation = $this->order->reviewInvitation()->firstOrCreate();

        $this->order->customer?->notify(new ReviewOrderInvitationNotification($this->order, $this->delayText));

        $invitation->update([
            'sent' => true,
            'sent_at' => Carbon::now(),
        ]);
    }
}
