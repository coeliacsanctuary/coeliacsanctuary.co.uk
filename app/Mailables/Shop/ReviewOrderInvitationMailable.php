<?php

declare(strict_types=1);

namespace App\Mailables\Shop;

use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class ReviewOrderInvitationMailable extends BaseShopMailable
{
    final public function __construct(protected ShopOrder $order, protected string $delayText, ?string $emailKey = null)
    {
        parent::__construct($order, $emailKey);
    }

    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject('Review your Coeliac Sanctuary Order!')
            ->mjml('mailables.mjml.shop.static.review-invitation', $this->baseData([
                'reviewLink' => $this->buildReviewLink(),
                'delayText' => $this->delayText,
                'reason' => 'to invite you to leave feedback on your recent purchase.',
            ]));
    }

    protected function buildReviewLink(): string
    {
        if ( ! $this->order->reviewInvitation || ! $this->order->customer) {
            throw new RuntimeException('Not valid order');
        }

        return URL::temporarySignedRoute(
            'shop.review-order',
            Carbon::now()->addMonths(6),
            [
                'invitation' => $this->order->reviewInvitation,
                'hash' => sha1($this->order->customer->email),
            ]
        );
    }
}
