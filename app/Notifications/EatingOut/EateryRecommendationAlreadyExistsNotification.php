<?php

declare(strict_types=1);

namespace App\Notifications\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\EatingOut\EateryRecommendationAlreadyExistsMailable;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\Shop\ShopCustomer;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

class EateryRecommendationAlreadyExistsNotification extends Notification
{
    public function __construct(protected EateryRecommendation $recommendation, protected Eatery $eatery)
    {
        //
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return EateryRecommendationAlreadyExistsMailable::make($this->eatery, $this->recommendation, $this->key);
    }
}
