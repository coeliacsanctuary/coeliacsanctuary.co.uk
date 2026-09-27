<?php

declare(strict_types=1);

namespace App\Notifications\Shop\MyTravelCards;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\CommentApprovedMailable;
use App\Mailables\Shop\MyTravelCards\OneTimeCodeMailable;
use App\Models\Comments\Comment;
use App\Models\Shop\ShopCustomer;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

class OneTimeCodeNotification extends Notification
{
    public function __construct(protected string $oneTimeCode, protected ShopCustomer $customer)
    {
        //
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return OneTimeCodeMailable::make($this->oneTimeCode, $this->customer, $this->key);
    }
}
