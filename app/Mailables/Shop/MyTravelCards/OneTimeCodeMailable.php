<?php

declare(strict_types=1);

namespace App\Mailables\Shop\MyTravelCards;

use App\Infrastructure\MjmlMessage;
use App\Mailables\BaseMailable;
use App\Models\Shop\ShopCustomer;

class OneTimeCodeMailable extends BaseMailable
{
    public function __construct(protected string $oneTimeCode, protected ShopCustomer $customer, ?string $emailKey = null)
    {
        parent::__construct($emailKey);
    }

    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject('Your one time code to access your Travel Cards')
            ->mjml('mailables.mjml.shop.my-travel-cards.confirm', $this->baseData([
                'customer' => $this->customer,
                'code' => $this->oneTimeCode,
                'reason' => 'to provide you with a one time access code to access your Digital Travel Cards.',
                'email' => $this->customer->email,
                'relatedItems' => null,
            ]));
    }
}
