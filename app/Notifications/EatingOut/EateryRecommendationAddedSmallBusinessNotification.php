<?php

declare(strict_types=1);

namespace App\Notifications\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\EatingOut\EateryRecommendationAddedSmallBusinessMailable;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\Shop\ShopCustomer;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

class EateryRecommendationAddedSmallBusinessNotification extends Notification
{
    public function __construct(protected EateryRecommendation $recommendation)
    {
        //
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return EateryRecommendationAddedSmallBusinessMailable::make($this->recommendation, $this->key);
    }
}
