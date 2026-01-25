<?php

declare(strict_types=1);

namespace App\Notifications\Shop;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\Shop\DownloadYourProductsMailable;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrderDownloadLink;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

class DownloadYourProductsNotification extends Notification
{
    public function __construct(protected ShopOrderDownloadLink $downloadLink)
    {
        //
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return DownloadYourProductsMailable::make($this->downloadLink, $this->key);
    }
}
